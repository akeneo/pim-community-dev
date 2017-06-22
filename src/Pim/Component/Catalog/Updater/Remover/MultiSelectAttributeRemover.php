<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Remove a data from a multi select field
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectAttributeRemover extends AbstractAttributeRemover
{
    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /**
     * @param AttributeValidatorHelper         $attrValidatorHelper
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param string[]                         $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->supportedTypes          = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkData($attribute, $data);

        $this->removeOptions($entityWithValues, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute
     * @param string[]                  $optionCodes
     * @param string|null               $locale
     * @param string|null               $scope
     */
    protected function removeOptions(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $optionCodes,
        $locale,
        $scope
    ) {
        $value = $entityWithValues->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $value) {
            $newOptionCodes = [];
            foreach ($value->getData() as $originalOption) {
                if (!in_array($originalOption->getCode(), $optionCodes)) {
                    $newOptionCodes[] = $originalOption->getCode();
                }
            }

            $this->entityWithValuesBuilder->addOrReplaceValue(
                $entityWithValues,
                $attribute,
                $locale,
                $scope,
                $newOptionCodes
            );
        }
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('one of the option codes is not a string, "%s" given', gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }
}
