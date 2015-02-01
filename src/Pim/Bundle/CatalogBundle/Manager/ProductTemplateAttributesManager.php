<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product template attributes manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO not comfortable with the naming of ProductTemplateAttributesManager we could maybe merge
 * ProductTemplateAttributesManager and ProductTemplateFactory to a ProductTemplateBuilder
 * with create(), addAttributes(), removeAttribute() methods
 */
class ProductTemplateAttributesManager
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $productClass;

    /**
     * @param NormalizerInterface          $normalizer
     * @param DenormalizerInterface        $denormalizer
     * @param ProductBuilder               $productBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string                       $productClass
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        ProductBuilder $productBuilder,
        AttributeRepositoryInterface $attributeRepository,
        $productClass
    ) {
        $this->normalizer          = $normalizer;
        $this->denormalizer        = $denormalizer;
        $this->productBuilder      = $productBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->productClass        = $productClass;
    }

    /**
     * Get non eligible attributes to a product template
     *
     * @param GroupInterface $group
     *
     * @return AttributeInterface[]
     */
    public function getNonEligibleAttributes(GroupInterface $group)
    {
        $attributes = $group->getAxisAttributes()->toArray();

        $template = $group->getProductTemplate();
        if (null !== $template) {
            foreach (array_keys($template->getValuesData()) as $attributeCode) {
                $attributes[] = $this->attributeRepository->findOneByIdentifier($attributeCode);
            }
        }

        $uniqueAttributes = $this->attributeRepository->findBy(['unique' => true]);
        foreach ($uniqueAttributes as $attribute) {
            if (!in_array($attribute, $attributes)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Add required value(s) that link an attribute to a product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface[]     $attributes
     */
    public function addAttributes(ProductTemplateInterface $template, array $attributes)
    {
        $values     = $this->buildProductValuesFromTemplateValuesData($template, $attributes);
        $valuesData = $this->normalizer->normalize($values, 'json', ['entity' => 'product']);
        $template->setValuesData($valuesData);
    }

    /**
     * Delete values that link an attribute to the product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface       $attribute
     */
    public function removeAttribute(ProductTemplateInterface $template, AttributeInterface $attribute)
    {
        $valuesData = $template->getValuesData();

        unset($valuesData[$attribute->getCode()]);

        $template->setValuesData($valuesData);
    }

    /**
     * Build product values from template values raw data
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface[]     $attributes
     *
     * @return ProductValueInterface[]
     */
    protected function buildProductValuesFromTemplateValuesData(ProductTemplateInterface $template, array $attributes)
    {
        $values  = $this->denormalizer->denormalize($template->getValuesData(), 'ProductValue[]', 'json');
        $product = new $this->productClass();

        foreach ($values as $value) {
            $product->addValue($value);
        }

        foreach ($attributes as $attribute) {
            $this->productBuilder->addAttributeToProduct($product, $attribute);
        }

        $this->productBuilder->addMissingProductValues($product);

        return $product->getValues();
    }
}
