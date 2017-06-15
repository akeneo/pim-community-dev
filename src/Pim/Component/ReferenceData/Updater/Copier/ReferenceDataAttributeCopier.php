<?php

namespace Pim\Component\ReferenceData\Updater\Copier;

use Pim\Component\Catalog\Builder\ValuesContainerBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;
use Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Copy a reference data value attribute in other reference data value attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataAttributeCopier extends AbstractAttributeCopier
{
    /**
     * @param ValuesContainerBuilderInterface $valuesContainerBuilder
     * @param AttributeValidatorHelper        $attrValidatorHelper
     * @param array                           $supportedFromTypes
     * @param array                           $supportedToTypes
     */
    public function __construct(
        ValuesContainerBuilderInterface $valuesContainerBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($valuesContainerBuilder, $attrValidatorHelper);

        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        ValuesContainerInterface $fromValuesContainer,
        ValuesContainerInterface $toValuesContainer,
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
            $fromValuesContainer,
            $toValuesContainer,
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
     * @param ValuesContainerInterface $fromValuesContainer
     * @param ValuesContainerInterface $toValuesContainer
     * @param AttributeInterface       $fromAttribute
     * @param AttributeInterface       $toAttribute
     * @param string|null              $fromLocale
     * @param string|null              $toLocale
     * @param string|null              $fromScope
     * @param string|null              $toScope
     */
    protected function copySingleValue(
        ValuesContainerInterface $fromValuesContainer,
        ValuesContainerInterface $toValuesContainer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $fromValuesContainer->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue && null !== $fromValue->getData()) {
            $this->valuesContainerBuilder->addOrReplaceValue(
                $toValuesContainer,
                $toAttribute,
                $toLocale,
                $toScope,
                $fromValue->getData()->getCode()
            );
        }
    }
}
