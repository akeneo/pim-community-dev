<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\ValuesContainerBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;
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
    /** @var ValuesContainerBuilderInterface */
    protected $valuesContainerBuilder;

    /**
     * @param AttributeValidatorHelper        $attrValidatorHelper
     * @param ValuesContainerBuilderInterface $valuesContainerBuilder
     * @param string[]                        $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ValuesContainerBuilderInterface $valuesContainerBuilder,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->valuesContainerBuilder = $valuesContainerBuilder;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkData($attribute, $data);

        $this->removeOptions($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * @param ValuesContainerInterface $valuesContainer
     * @param AttributeInterface       $attribute
     * @param string[]                 $optionCodes
     * @param string|null              $locale
     * @param string|null              $scope
     */
    protected function removeOptions(
        ValuesContainerInterface $valuesContainer,
        AttributeInterface $attribute,
        $optionCodes,
        $locale,
        $scope
    ) {
        $value = $valuesContainer->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $value) {
            $newOptionCodes = [];
            foreach ($value->getData() as $originalOption) {
                if (!in_array($originalOption->getCode(), $optionCodes)) {
                    $newOptionCodes[] = $originalOption->getCode();
                }
            }

            $this->valuesContainerBuilder->addOrReplaceValue(
                $valuesContainer,
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
