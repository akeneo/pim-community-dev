<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
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
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope']);
        $this->checkData($attribute, $data);

        $this->addPrices($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Check if data are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
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

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidPropertyTypeException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    static::class,
                    $data
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    static::class,
                    $data
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    static::class,
                    $data
                );
            }

            if (!is_numeric($price['amount']) && null !== $price['amount']) {
                throw new InvalidPropertyTypeException(
                    $attribute->getCode(),
                    $price['amount'],
                    static::class,
                    sprintf(
                        'Property "%s" expects a numeric as data for the currency, "%s" given.',
                        $attribute->getCode(),
                        $price['amount']
                    ),
                    InvalidPropertyTypeException::NUMERIC_EXPECTED_CODE
                );
            }

            if (!in_array($price['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'currency code',
                    'The currency does not exist',
                    static::class,
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
            $value = $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope);
        }

        foreach ($data as $price) {
            $this->productBuilder->addPriceForCurrency($value, $price['currency'], $price['amount']);
        }
    }
}
