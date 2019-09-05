<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
        $value = (null !== $fromValue && null !== $fromValue->getData()) ? $fromValue->getData() : null;
        $this->entityWithValuesBuilder->addOrReplaceValue(
            $toEntityWithValues,
            $toAttribute,
            $toLocale,
            $toScope,
            $value
        );
    }
}
