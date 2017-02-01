<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
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
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param ProductBuilderInterface  $productBuilder
     * @param string[]                 $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ProductBuilderInterface $productBuilder,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->productBuilder = $productBuilder;
        $this->supportedTypes = $supportedTypes;
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
        $this->checkData($attribute, $data);

        $this->removeOptions($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param string[]           $optionCodes
     * @param string|null        $locale
     * @param string|null        $scope
     */
    protected function removeOptions(
        ProductInterface $product,
        AttributeInterface $attribute,
        $optionCodes,
        $locale,
        $scope
    ) {
        $productValue = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $productValue) {
            $newOptionCodes = [];
            foreach ($productValue->getData() as $originalOption) {
                if (!in_array($originalOption->getCode(), $optionCodes)) {
                    $newOptionCodes[] = $originalOption->getCode();
                }
            }

            $this->productBuilder->addProductValue($product, $attribute, $locale, $scope, $newOptionCodes);
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
}
