<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * Sets a multi select value in many entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectAttributeAdder extends AbstractAttributeAdder
{
    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param array                            $supportedTypes
     */
    public function __construct(EntityWithValuesBuilderInterface $entityWithValuesBuilder, array $supportedTypes)
    {
        parent::__construct($entityWithValuesBuilder);

        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format: ["option_code", "other_option_code"]
     */
    public function addAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $this->addOptions($entityWithValues, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Adds options into the value
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute
     * @param array                     $optionCodes
     * @param string                    $locale
     * @param string                    $scope
     */
    protected function addOptions(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        array $optionCodes,
        $locale,
        $scope
    ) {
        $optionsValue = $entityWithValues->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $optionsValue) {
            foreach ($optionsValue->getOptionCodes() as $optionValue) {
                if (!in_array($optionValue, $optionCodes)) {
                    $optionCodes[] = $optionValue;
                }
            }
        }

        $this->entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, $locale, $scope, $optionCodes);
    }
}
