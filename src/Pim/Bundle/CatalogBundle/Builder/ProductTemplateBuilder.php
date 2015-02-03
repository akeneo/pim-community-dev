<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product template builder, allows to create new product template and update them
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateBuilder implements ProductTemplateBuilderInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var string */
    protected $productTemplateClass;

    /** @var string */
    protected $productClass;

    /**
     * @param NormalizerInterface   $normalizer
     * @param DenormalizerInterface $denormalizer
     * @param ProductBuilder        $productBuilder
     * @param string                $productTemplateClass
     * @param string                $productClass
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        ProductBuilder $productBuilder,
        $productTemplateClass,
        $productClass
    ) {
        $this->normalizer           = $normalizer;
        $this->denormalizer         = $denormalizer;
        $this->productBuilder       = $productBuilder;
        $this->productTemplateClass = $productTemplateClass;
        $this->productClass         = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductTemplate()
    {
        return new $this->productTemplateClass();
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributes(ProductTemplateInterface $template, array $attributes)
    {
        $values     = $this->buildProductValuesFromTemplateValuesData($template, $attributes);
        $valuesData = $this->normalizer->normalize($values, 'json', ['entity' => 'product']);
        $template->setValuesData($valuesData);
    }

    /**
     * {@inheritdoc}
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
