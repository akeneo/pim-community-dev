<?php

namespace Pim\Component\ReferenceData\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier;
use Pim\Component\ReferenceData\MethodNameGuesser;

/**
 * Copy a reference data collection value attribute in other reference data collection value attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionAttributeCopier extends AbstractAttributeCopier
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
        $this->supportedToTypes   = $supportedToTypes;
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
        $options    = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale   = $options['to_locale'];
        $fromScope  = $options['from_scope'];
        $toScope    = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope, 'reference data collection');
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope, 'reference data collection');

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
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom  = in_array($fromAttribute->getAttributeType(), $this->supportedFromTypes);
        $supportsTo    = in_array($toAttribute->getAttributeType(), $this->supportedToTypes);
        $referenceData = ($fromAttribute->getReferenceDataName() === $toAttribute->getReferenceDataName());

        return $supportsFrom && $supportsTo && $referenceData;
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

            $this->removeReferenceDataCollection($toValue, $toAttribute);
            $this->copyReferenceDataCollection($fromValue, $toValue, $fromAttribute, $toAttribute);
        }
    }

    /**
     * Remove reference data collection from attribute
     *
     * @param ProductValueInterface $toValue
     * @param AttributeInterface    $toAttribute
     */
    protected function removeReferenceDataCollection(ProductValueInterface $toValue, AttributeInterface $toAttribute)
    {
        $toDataGetter  = $this->getValueMethodName($toValue, $toAttribute, 'get');
        $toDataRemover = $this->getValueMethodName($toValue, $toAttribute, 'remove', true);

        foreach ($toValue->$toDataGetter() as $attributeOption) {
            $toValue->$toDataRemover($attributeOption);
        }
    }

    /**
     * Copy attribute reference data collection into a reference data collection attribute
     *
     * @param ProductValueInterface $fromValue
     * @param ProductValueInterface $toValue
     * @param AttributeInterface    $fromAttribute
     * @param AttributeInterface    $toAttribute
     */
    protected function copyReferenceDataCollection(
        ProductValueInterface $fromValue,
        ProductValueInterface $toValue,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute
    ) {
        $fromDataGetter = $this->getValueMethodName($fromValue, $fromAttribute, 'get');
        $toDataAdder    = $this->getValueMethodName($toValue, $toAttribute, 'add', true);

        foreach ($fromValue->$fromDataGetter() as $attributeOption) {
            $toValue->$toDataAdder($attributeOption);
        }
    }

    /**
     * @param ProductValueInterface $value
     * @param AttributeInterface    $attribute
     * @param string                $type
     * @param bool                  $singularify
     *
     * @return string
     */
    private function getValueMethodName(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        $type,
        $singularify = false
    ) {
        $method = MethodNameGuesser::guess($type, $attribute->getReferenceDataName(), $singularify);

        if (!method_exists($value, $method)) {
            throw new \LogicException(
                sprintf('ProductValue method "%s" is not implemented', $method)
            );
        }

        return $method;
    }
}
