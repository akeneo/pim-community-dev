<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Sets a multi select value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectAttributeAdder extends AbstractAttributeAdder
{
    /**
     * @param ProductBuilderInterface $productBuilder
     * @param array                   $supportedTypes
     */
    public function __construct(ProductBuilderInterface $productBuilder, array $supportedTypes)
    {
        parent::__construct($productBuilder);

        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format: ["option_code", "other_option_code"]
     */
    public function addAttributeData(
        ProductInterface $product,
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

        $this->addOptions($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Adds options into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param array              $optionCodes
     * @param string             $locale
     * @param string             $scope
     */
    protected function addOptions(
        ProductInterface $product,
        AttributeInterface $attribute,
        array $optionCodes,
        $locale,
        $scope
    ) {
        $optionsValue = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $optionsValue) {
            foreach ($optionsValue->getOptionCodes() as $optionValue) {
                if (!in_array($optionValue, $optionCodes)) {
                    $optionCodes[] = $optionValue;
                }
            }
        }

        $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope, $optionCodes);
    }
}
