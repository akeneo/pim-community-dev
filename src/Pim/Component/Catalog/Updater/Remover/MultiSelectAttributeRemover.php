<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
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
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attrOptionRepository;

    /**
     * @param AttributeValidatorHelper              $attrValidatorHelper
     * @param IdentifiableObjectRepositoryInterface $attrOptionRepository
     * @param string[]                              $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        IdentifiableObjectRepositoryInterface $attrOptionRepository,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->attrOptionRepository = $attrOptionRepository;
        $this->supportedTypes       = $supportedTypes;
    }

    /**
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param array              $data
     * @param array              $options
     */
    public function removeAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'multi-select');
        $this->checkData($attribute, $data);

        $attributeOptions = [];
        foreach ($data as $optionCode) {
            $option = $this->getOption($attribute, $optionCode);
            if (null === $option) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'code',
                    'The option does not exist',
                    'remover',
                    'multi select',
                    $optionCode
                );
            }

            $attributeOptions[] = $option;
        }

        $this->removeOptions($product, $attribute, $attributeOptions, $options['locale'], $options['scope']);
    }

    /**
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param array              $attributeOptions
     * @param string|null        $locale
     * @param string|null        $scope
     */
    protected function removeOptions(
        ProductInterface $product,
        AttributeInterface $attribute,
        $attributeOptions,
        $locale,
        $scope
    ) {
        $productValue = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $productValue) {
            foreach ($attributeOptions as $attributeOption) {
                $productValue->removeOption($attributeOption);
            }
        }
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'remover',
                'multi select',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringValueExpected(
                    $attribute->getCode(),
                    $key,
                    'remover',
                    'multi select',
                    gettype($value)
                );
            }
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
