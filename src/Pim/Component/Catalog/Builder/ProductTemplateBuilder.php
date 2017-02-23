<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;

/**
 * Product template builder, allows to create new product template and update them
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateBuilder implements ProductTemplateBuilderInterface
{
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var string */
    protected $productTemplateClass;

    /**
     * @param ProductBuilderInterface $productBuilder
     * @param string                  $productTemplateClass
     */
    public function __construct(ProductBuilderInterface $productBuilder, $productTemplateClass)
    {
        $this->productBuilder = $productBuilder;
        $this->productTemplateClass = $productTemplateClass;
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
        $values = $this->buildProductValuesFromTemplateValuesData($template, $attributes);
        $template->setValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttribute(ProductTemplateInterface $template, AttributeInterface $attribute)
    {
        $template->getValues()->removeByAttribute($attribute);
    }

    /**
     * Build product values from template values raw data
     *
     * @param ProductTemplateInterface $template
     * @param AttributeInterface[]     $attributes
     *
     * @return ProductValueCollectionInterface
     */
    protected function buildProductValuesFromTemplateValuesData(
        ProductTemplateInterface $template,
        array $attributes
    ) {
        $product = $this->productBuilder->createProduct();
        $product->setValues($template->getValues());

        foreach ($attributes as $attribute) {
            $this->productBuilder->addAttributeToProduct($product, $attribute);
        }

        $this->productBuilder->addMissingProductValues($product);

        return $product->getValues();
    }
}
