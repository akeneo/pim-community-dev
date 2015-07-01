<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Copy a multi select value attribute in other multi select value attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectAttributeCopier extends AbstractAttributeCopier
{
    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedFromTypes
     * @param array                    $supportedToTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope, 'multi select');
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope, 'multi select');

        $this->copySingleValue(
            $fromProduct,
            $toProduct,
            $fromAttribute,
            $toAttribute,
            $fromLocale,
            $toLocale,
            $fromScope,
            $toScope
        );
    }

    /**
     * Copy single value
     *
     * @param ProductInterface   $fromProduct
     * @param ProductInterface   $toProduct
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     */
    protected function copySingleValue(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $fromProduct->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $toValue = $toProduct->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($toProduct, $toAttribute, $toLocale, $toScope);
            }

            $this->removeOptions($toValue);
            $this->copyOptions($fromValue, $toValue);
        }
    }

    /**
     * Remove options from attribute
     *
     * @param ProductValueInterface $toValue
     */
    protected function removeOptions(ProductValueInterface $toValue)
    {
        foreach ($toValue->getOptions() as $attributeOption) {
            $toValue->removeOption($attributeOption);
        }
    }

    /**
     * Copy attribute options into a multi select attribute
     *
     * @param ProductValueInterface $fromValue
     * @param ProductValueInterface $toValue
     */
    protected function copyOptions(ProductValueInterface $fromValue, ProductValueInterface $toValue)
    {
        foreach ($fromValue->getOptions() as $attributeOption) {
            $toValue->addOption($attributeOption);
        }
    }
}
