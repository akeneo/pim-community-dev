<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollection;

/**
 * Factory that creates price collection product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionProductValueFactory implements ProductValueFactoryInterface
{
    /** @var PriceFactory */
    protected $priceFactory;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param PriceFactory $priceFactory
     * @param string       $productValueClass
     * @param string       $supportedAttributeType
     */
    public function __construct(PriceFactory $priceFactory, $productValueClass, $supportedAttributeType)
    {
        $this->priceFactory = $priceFactory;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass(
            $attribute, $channelCode, $localeCode, $this->getPrices($attribute, $data)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks if data are valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

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
        }
    }

    /**
     * Gets a collection of price from prices in standard format.
     *
     * @param AttributeInterface $attribute
     * @param array              $data
     *
     * @return PriceCollection
     */
    protected function getPrices(AttributeInterface $attribute, array $data)
    {
        $prices = new PriceCollection();

        $filteredData = $this->filterByCurrency($data);
        $sortedData = $this->sortByCurrency($filteredData);
        foreach ($sortedData as $price) {
            try {
                $newPrice = $this->priceFactory->createPrice($price['amount'], $price['currency']);
            } catch (InvalidPropertyException $e) {
                throw InvalidPropertyException::expectedFromPreviousException($attribute->getCode(), self::class, $e);
            }

            $prices->add($newPrice);
        }

        return $prices;
    }

    /**
     * Sorts the array of prices data by their currency.
     *
     * For example:
     *
     * [
     *     [
     *         'amount'   => 20,
     *         'currency' => 'USD',
     *     ],
     *     [
     *         'amount'   => 100,
     *         'currency' => 'EUR',
     *     ],
     * ]
     *
     * will become:
     *
     * [
     *     [
     *         'amount'   => 100,
     *         'currency' => 'EUR',
     *     ],
     *     [
     *         'amount'   => 20,
     *         'currency' => 'USD',
     *     ],
     * ]
     *
     * @param array $arrayPrices
     *
     * @return array
     */
    protected function sortByCurrency(array $arrayPrices)
    {
        $amounts = [];
        $currencies = [];

        foreach ($arrayPrices as $price) {
            $amounts[] = $price['amount'];
            $currencies[] = $price['currency'];
        }

        $sort = array_multisort($currencies, SORT_ASC, $amounts, SORT_ASC, $arrayPrices);

        if (false === $sort) {
            throw new \LogicException(
                sprintf('Impossible to perform multisort on the following array: %s', json_encode($arrayPrices)),
                0,
                static::class
            );
        }

        return $arrayPrices;
    }

    /**
     * Checks that for each currency there is only one value. If it's the case, this method keeps the last value
     * for the duplicated currency in the given array
     *
     * @param array $data
     *
     * @return array
     */
    protected function filterByCurrency(array $data)
    {
        $uniqueData = [];
        $filteredData = [];

        foreach ($data as $price) {
            $uniqueData[$price['currency']] = $price['amount'];
        }

        foreach ($uniqueData as $currency => $price) {
            $filteredData[] = ['currency' => $currency, 'amount' => $price];
        }

        return $filteredData;
    }
}
