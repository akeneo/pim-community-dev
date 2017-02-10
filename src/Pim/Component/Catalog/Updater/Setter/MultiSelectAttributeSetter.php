<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Sets a multi select value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectAttributeSetter extends AbstractAttributeSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attrOptionRepository;

    /**
     * @param ProductBuilderInterface               $productBuilder
     * @param AttributeValidatorHelper              $attrValidatorHelper
     * @param IdentifiableObjectRepositoryInterface $attrOptionRepository
     * @param array                                 $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        IdentifiableObjectRepositoryInterface $attrOptionRepository,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->attrOptionRepository = $attrOptionRepository;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format: ["option_code", "other_option_code"]
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope']);
        $this->checkData($attribute, $data);

        $attributeOptions = [];
        foreach ($data as $optionCode) {
            $option = $this->getOption($attribute, $optionCode);
            if (null === $option) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'code',
                    'The option does not exist',
                    static::class,
                    $optionCode
                );
            }

            $attributeOptions[] = $option;
        }

        $this->setOptions($product, $attribute, $attributeOptions, $options['locale'], $options['scope']);
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
                    sprintf('one of the options is not a string, "%s" given', gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Set options into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param array              $attributeOptions
     * @param string             $locale
     * @param string             $scope
     */
    protected function setOptions(
        ProductInterface $product,
        AttributeInterface $attribute,
        $attributeOptions,
        $locale,
        $scope
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope);
        }

        foreach ($value->getOptions() as $option) {
            $value->removeOption($option);
        }

        foreach ($attributeOptions as $attributeOption) {
            $value->addOption($attributeOption);
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $optionCode
     *
     * @return AttributeOptionInterface|null
     */
    protected function getOption(AttributeInterface $attribute, $optionCode)
    {
        $identifier = $attribute->getCode() . '.' . $optionCode;
        $option = $this->attrOptionRepository->findOneByIdentifier($identifier);

        return $option;
    }
}
