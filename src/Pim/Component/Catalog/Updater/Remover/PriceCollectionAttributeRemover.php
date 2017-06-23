<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Removes a price attribute (a currency) from an entity with values
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeRemover extends AbstractAttributeRemover
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /**
     * @param AttributeValidatorHelper         $attrValidatorHelper
     * @param CurrencyRepositoryInterface      $currencyRepository
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param string[]                         $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->currencyRepository      = $currencyRepository;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->supportedTypes          = $supportedTypes;
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
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkData($attribute, $data);

        $this->removePrices($entityWithValues, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Remove prices from the given $entityWithValues
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute
     * @param mixed                     $data
     * @param string                    $locale
     * @param string                    $scope
     */
    protected function removePrices(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        $locale,
        $scope
    ) {
        $productValue = $entityWithValues->getValue($attribute->getCode(), $locale, $scope);

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

            $this->entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, $locale, $scope, $prices);
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
