<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Removes a price attribute (a currency) from a product
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeRemover extends AbstractAttributeRemover
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param AttributeValidatorHelper    $attrValidatorHelper
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param ProductBuilderInterface     $productBuilder
     * @param string[]                    $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        ProductBuilderInterface $productBuilder,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->currencyRepository = $currencyRepository;
        $this->productBuilder = $productBuilder;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format:
     * [
     *     {
     *         "amount": "12.0"|12|null,
     *         "currency": "EUR"
     *     },
     *     {
     *         "amount": "12.0"|12|null,
     *         "currency": "USD"
     *     }
     * ]
     * "data" index is not used so it can be null
     */
    public function removeAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkData($attribute, $data);

        $this->removePrices($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Remove prices from product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function removePrices(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $productValue = $product->getValue($attribute->getCode(), $locale, $scope);

        $currencyToRemove = [];
        foreach ($data as $priceToRemove) {
            $currencyToRemove[] = $priceToRemove['currency'];
        }

        if (null !== $productValue) {
            $prices = [];
            foreach ($productValue->getData() as $price) {
                if (!in_array($price->getCurrency(), $currencyToRemove)) {
                    $prices[] = ['amount' => $price->getData(), 'currency' => $price->getCurrency()];
                }
            }

            $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope, $prices);
        }
    }

    /**
     * Check if data are valid
     * "data": doesn't need value
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
}
