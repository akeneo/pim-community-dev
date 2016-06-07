<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat\Product;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

/**
 * Standard to flat array converter for product value
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductValueConverter
{
    /** @var AttributeColumnsResolver */
    protected $columnsResolver;

    /**
     * @param AttributeColumnsResolver $columnsResolver
     */
    public function __construct(AttributeColumnsResolver $columnsResolver)
    {
        $this->columnsResolver = $columnsResolver;
    }

    /**
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    public function convertField($field, $data)
    {
        $convertedItem = [];
        $isConverted = false;

        if ($this->isBoolean($data)) {
            $isConverted = true;
            $convertedItem = $this->convertBooleanField($field, $data, $convertedItem);
        }

        if ($this->isMetric($data)) {
            $isConverted = true;
            $convertedItem = $this->convertMetricField($field, $data, $convertedItem);
        }

        if ($this->isPrice($data)) {
            $isConverted = true;
            $convertedItem = $this->convertPriceField($field, $data, $convertedItem);
        }

        if (!$isConverted) {
            $convertedItem = $this->convertDefaultField($field, $data, $convertedItem);
        }

        return $convertedItem;
    }

    /**
     * Return whether the given $data is a boolean.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isBoolean(array $data)
    {
        $firstData = current($data);

        return is_bool($firstData['data']);
    }

    /**
     * Return whether the given $data is a metric.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isMetric(array $data)
    {
        $firstData = current($data);

        return is_array($firstData) && is_array($firstData['data']) && isset($firstData['data']['unit']);
    }

    /**
     * Return whether the given $data is a price.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isPrice(array $data)
    {
        $firstData = current($data);

        if (!is_array($firstData) || !is_array($firstData['data'])) {
            return false;
        }

        $firstCurrency = current($firstData['data']);

        return isset($firstCurrency['currency']);
    }

    /**
     * Convert a standard formatted boolean field to a flat one.
     *
     * Given a 'auto_lock' $field with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'print',
     *         'data'   => false
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'auto_lock-de_DE-print' => '0',
     * ]
     *
     * @param string $field
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertBooleanField($field, array $data, array $convertedItem)
    {
        foreach ($data as $value) {
            $fieldName = $this->columnsResolver->resolveFlatAttributeName($field, $value['locale'], $value['scope']);

            $convertedItem[$fieldName] = (true === $value['data']) ? '1' : '0';
        }

        return $convertedItem;
    }

    /**
     * Convert a standard formatted metric field to a flat one.
     *
     * Given a 'weight' $field with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'print',
     *         'data'   => [
     *             'unit' => 'MEGAHERTZ',
     *             'data' => '100'
     *         ]
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'weight-de_DE-print'      => '100',
     *     'weight-de_DE-print-unit' => 'MEGAHERTZ',
     * ]
     *
     * @param string $field
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertMetricField($field, array $data, array $convertedItem)
    {
        foreach ($data as $value) {
            $fieldName = $this->columnsResolver->resolveFlatAttributeName($field, $value['locale'], $value['scope']);
            $fieldUnitName = sprintf('%s-unit', $fieldName);

            $convertedItem[$fieldName]     = $value['data']['data'];
            $convertedItem[$fieldUnitName] = $value['data']['unit'];
        }

        return $convertedItem;
    }

    /**
     * Convert a standard formatted price field to a flat one.
     *
     * Given a 'super_price' $field with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'ecommerce',
     *         'data'   => [
     *             [
     *                 'data'     => '10',
     *                 'currency' => 'EUR'
     *             ],
     *             [
     *                 'data'     => '9',
     *                 'currency' => 'USD'
     *             ],
     *         ]
     *     ],
     *     [
     *         'locale' => 'fr_FR',
     *         'scope'  => 'ecommerce',
     *         'data'   => [
     *             [
     *                 'data'     => '30',
     *                 'currency' => 'EUR'
     *             ],
     *             [
     *                 'data'     => '29',
     *                 'currency' => 'USD'
     *             ],
     *         ]
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'super_price-de_DE-ecommerce-EUR' => '10',
     *     'super_price-de_DE-ecommerce-USD' => '9',
     *     'super_price-fr_FR-ecommerce-EUR' => '30',
     *     'super_price-fr_FR-ecommerce-USD' => '29',
     * ]
     *
     * @param string $field
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertPriceField($field, array $data, array $convertedItem)
    {
        foreach ($data as $value) {
            $fieldName = $this->columnsResolver->resolveFlatAttributeName($field, $value['locale'], $value['scope']);

            foreach ($value['data'] as $currency) {
                $currencyFieldName = sprintf('%s-%s', $fieldName, $currency['currency']);
                $convertedItem[$currencyFieldName] = $currency['data'];
            }
        }

        return $convertedItem;
    }

    /**
     * Convert a standard formatted non-metric/non-price field to a flat one.
     *
     * Given a 'tshirt_materials' $field with this $data:
     * [
     *     [
     *         'locale' => null,
     *         'scope'  => 'print',
     *         'data'   => ['silk', 'gold']
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'tshirt_materials-print' => 'silk,gold',
     * ]
     *
     * @param string $field
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertDefaultField($field, array $data, array $convertedItem)
    {
        foreach ($data as $value) {
            $flatField = $this->columnsResolver->resolveFlatAttributeName($field, $value['locale'], $value['scope']);
            $newValue = is_array($value['data']) ? implode(',', $value['data']) : $value['data'];

            $convertedItem[$flatField] = $newValue;
        }

        return $convertedItem;
    }
}
