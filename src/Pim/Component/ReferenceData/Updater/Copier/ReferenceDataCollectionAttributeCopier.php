<?php

namespace Pim\Component\ReferenceData\Updater\Copier;

use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

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
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param AttributeValidatorHelper         $attrValidatorHelper
     * @param array                            $supportedFromTypes
     * @param array                            $supportedToTypes
     */
    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($entityWithValuesBuilder, $attrValidatorHelper);

        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

        $this->copySingleValue(
            $fromEntityWithValues,
            $toEntityWithValues,
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
        $supportsFrom = in_array($fromAttribute->getType(), $this->supportedFromTypes);
        $supportsTo = in_array($toAttribute->getType(), $this->supportedToTypes);
        $referenceData = ($fromAttribute->getReferenceDataName() === $toAttribute->getReferenceDataName());

        return $supportsFrom && $supportsTo && $referenceData;
    }

    /**
     * Copy single value
     *
     * @param EntityWithValuesInterface $fromEntityWithValues
     * @param EntityWithValuesInterface $toEntityWithValues
     * @param AttributeInterface        $fromAttribute
     * @param AttributeInterface        $toAttribute
     * @param string                    $fromLocale
     * @param string                    $toLocale
     * @param string                    $fromScope
     * @param string                    $toScope
     */
    protected function copySingleValue(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $fromEntityWithValues->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $this->entityWithValuesBuilder->addOrReplaceValue(
                $toEntityWithValues,
                $toAttribute,
                $toLocale,
                $toScope,
                $this->getReferenceDataCodes($fromValue)
            );
        }
    }

    /**
     * Gets the list of reference data codes contained in a product value collection.
     *
     * @param ValueInterface $fromValue
     *
     * @return string[]
     */
    protected function getReferenceDataCodes(ValueInterface $fromValue)
    {
        $referenceDataCodes = [];
        foreach ($fromValue->getData() as $referenceData) {
            $referenceDataCodes[] = $referenceData->getCode();
        }

        return $referenceDataCodes;
    }
}
