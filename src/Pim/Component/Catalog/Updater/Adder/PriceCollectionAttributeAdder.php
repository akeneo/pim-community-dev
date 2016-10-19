<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Price collection attribute adder
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeAdder extends AbstractAttributeAdder
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param ProductBuilderInterface     $productBuilder
     * @param AttributeValidatorHelper    $attrValidatorHelper
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param array                       $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);

        $this->currencyRepository = $currencyRepository;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format:
     * [
     *     {
     *         "data": "12.0"|"12"|12|12.3,
     *         "currency": "EUR"
     *     },
     *     {
     *         "data": "12.0"|"12"|12|12.3,
     *         "currency": "EUR"
     *     }
     * ]
     */
    public function addAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'prices collection');
        $this->checkData($attribute, $data);

        $this->addPrices($product, $attribute, $data, $options['locale'], $options['scope']);
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
                'adder',
                'prices collection',
                gettype($data)
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidArgumentException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    'adder',
                    'prices collection',
                    gettype($data)
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    'adder',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    'adder',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!is_numeric($price['amount']) && null !== $price['amount']) {
                throw InvalidArgumentException::arrayNumericKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    'adder',
                    'prices collection',
                    gettype($price['amount'])
                );
            }

            if (!in_array($price['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'currency',
                    'The currency does not exist',
                    'adder',
                    'prices collection',
                    $price['currency']
                );
            }
        }
    }

    /**
     * Add prices into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function addPrices(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        foreach ($data as $price) {
            $this->productBuilder->addPriceForCurrencyWithData($value, $price['currency'], $price['amount']);
        }
    }
}
