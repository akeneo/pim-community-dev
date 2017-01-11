<?php

namespace Pim\Component\ReferenceData\Updater\Copier;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
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
        $supportsFrom = in_array($fromAttribute->getAttributeType(), $this->supportedFromTypes);
        $supportsTo = in_array($toAttribute->getAttributeType(), $this->supportedToTypes);
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
            if (null !== $toValue) {
                $toProduct->removeValue($toValue);
            }
            $fromDataGetter = $this->getValueGetterName($fromValue, $fromAttribute);

            $this->productBuilder->addProductValue(
                $toProduct,
                $toAttribute,
                $toLocale,
                $toScope,
                $fromValue->$fromDataGetter()
            );
        }
    }

    /**
     * @param ProductValueInterface $value
     * @param AttributeInterface    $attribute
     *
     * @return string
     */
    private function getValueGetterName(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $method = MethodNameGuesser::guess('get', $attribute->getReferenceDataName());

        if (!method_exists($value, $method)) {
            throw new \LogicException(
                sprintf('ProductValue method "%s" is not implemented', $method)
            );
        }

        return $method;
    }
}
