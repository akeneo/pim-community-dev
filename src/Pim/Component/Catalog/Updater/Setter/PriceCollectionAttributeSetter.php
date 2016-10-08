<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Sets a price collection value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeSetter extends AbstractAttributeSetter
{
    /**
     * @param \Pim\Component\Catalog\Builder\ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format:
     * [
     *     {
     *         "amount": "12.0"|"12"|12|12.3,
     *         "currency": "EUR"
     *     },
     *     {
     *         "amount": "12.0"|"12"|12|12.3,
     *         "currency": "EUR"
     *     }
     * ]
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'prices collection');
        $this->checkData($attribute, $data);

        $this->setPrices($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Check if data are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @return mixed
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'setter',
                'prices collection',
                gettype($data)
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidArgumentException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    'setter',
                    'prices collection',
                    gettype($data)
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    'setter',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    'setter',
                    'prices collection',
                    print_r($data, true)
                );
            }
        }
    }

    /**
     * Set prices into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function setPrices(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        } else {
            $prices = $value->getPrices();
            foreach ($prices as $price) {
                $price->setData(null);
            }
        }

        foreach ($data as $price) {
            $this->productBuilder->addPriceForCurrencyWithData($value, $price['currency'], $price['amount']);
        }
    }
}
