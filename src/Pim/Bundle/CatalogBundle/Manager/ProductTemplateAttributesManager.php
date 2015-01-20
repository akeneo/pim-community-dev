<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product template attributes manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateAttributesManager
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var string */
    protected $productClass;

    /**
     * @param NormalizerInterface   $normalizer
     * @param DenormalizerInterface $denormalizer
     * @param ProductBuilder        $productBuilder
     * @param string                $productClass
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        ProductBuilder $productBuilder,
        $productClass
    ) {
        $this->normalizer     = $normalizer;
        $this->denormalizer   = $denormalizer;
        $this->productBuilder = $productBuilder;
        $this->productClass   = $productClass;
    }

    /**
     * Adds required value(s) that link an attribute to a product template
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface[]     $attributes
     */
    public function addAttributes(ProductTemplateInterface $template, $attributes)
    {
        $valuesData = $template->getValuesData();
        $values = $this->denormalizer->denormalize($valuesData, 'ProductValue[]', 'json');

        $product = new $this->productClass();

        foreach ($values as $value) {
            $product->addValue($value);
        }

        foreach ($attributes as $attribute) {
            $this->productBuilder->addAttributeToProduct($product, $attribute);
        }

        $this->productBuilder->addMissingProductValues($product);

        $valuesData = $this->normalizer->normalize($product->getValues(), 'json', ['entity' => 'product']);
        $template->setValuesData($valuesData);
    }

    /**
     * Deletes values that link an attribute to the product template
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
}
