<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Merge columns for single value that can be provided in many columns like prices and metric
 *
 * These two values supports two different formats, we standardize here to the one column format
 *
 * For Prices
 *   - '10 EUR, 24 USD' or
 * or
 *   - 'price-EUR': '10',
 *   - 'price-USD': '24',
 *
 * For Metrics
 *   - 'weight': '10 KILOGRAM',
 * or
 *   - 'weight': '10',
 *   - 'weight-unit': 'KILOGRAM',
 *   - 'weight': '24'
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnsMerger
{
    /** @var AttributeColumnInfoExtractor */
    protected $fieldExtractor;

    /**
     * @param AttributeColumnInfoExtractor $fieldExtractor
     */
    public function __construct(AttributeColumnInfoExtractor $fieldExtractor)
    {
        $this->fieldExtractor = $fieldExtractor;
    }

    /**
     * @param array $row the item to merge
     *
     * @return array merged $row
     */
    public function merge(array $row)
    {
        $resultRow = [];
        $collectedMetrics = [];
        $collectedPrices = [];
        foreach ($row as $fieldName => $fieldValue) {
            $attributeInfos = $this->fieldExtractor->extractColumnInfo($fieldName);
            if (null !== $attributeInfos) {
                $attribute = $attributeInfos['attribute'];
                if (AttributeTypes::BACKEND_TYPE_METRIC === $attribute->getBackendType()) {
                    $collectedMetrics = $this->collectMetricData($collectedMetrics, $attributeInfos, $fieldValue);
                } elseif (AttributeTypes::BACKEND_TYPE_PRICE === $attribute->getBackendType()) {
                    $collectedPrices = $this->collectPriceData($collectedPrices, $attributeInfos, $fieldValue);
                } else {
                    $resultRow[$fieldName] = $fieldValue;
                }
            } else {
                $resultRow[$fieldName] = $fieldValue;
            }
        }

        $resultRow = $this->mergeMetricData($resultRow, $collectedMetrics);
        $resultRow = $this->mergePriceData($resultRow, $collectedPrices);

        return $resultRow;
    }

    /**
     * Returns a clean field name with code, locale and scope (without unit, currency, etc in the field)
     *
     * @param array $attributeInfos
     *
     * @return string
     */
    protected function getCleanFieldName(array $attributeInfos)
    {
        $attribute = $attributeInfos['attribute'];
        $cleanField = $attribute->getCode();
        $cleanField .= (null === $attributeInfos['locale_code']) ?
            '' : AttributeColumnInfoExtractor::FIELD_SEPARATOR.$attributeInfos['locale_code'];
        $cleanField .= (null === $attributeInfos['scope_code']) ?
            '' : AttributeColumnInfoExtractor::FIELD_SEPARATOR.$attributeInfos['scope_code'];

        return $cleanField;
    }

    /**
     * Collect metric data exploded in different columns
     *
     * @param array  $collectedMetrics
     * @param array  $attributeInfos
     * @param string $fieldValue
     *
     * @return array collected metrics
     */
    protected function collectMetricData(array $collectedMetrics, array $attributeInfos, $fieldValue)
    {
        $cleanField = $this->getCleanFieldName($attributeInfos);

        if (!in_array($cleanField, array_keys($collectedMetrics))) {
            $collectedMetrics[$cleanField] = ['data' => '', 'unit' => ''];
        }
        if ('unit' === $attributeInfos['metric_unit']) {
            $collectedMetrics[$cleanField]['unit'] = $fieldValue;
        } else {
            $collectedMetrics[$cleanField]['data'] = $fieldValue;
        }

        return $collectedMetrics;
    }

    /**
     * Merge collected metric in result rows
     *
     * @param array $resultRow
     * @param array $collectedMetrics
     *
     * @return array
     */
    protected function mergeMetricData(array $resultRow, array $collectedMetrics)
    {
        foreach ($collectedMetrics as $fieldName => $metricData) {
            $resultRow[$fieldName] = trim(
                sprintf(
                    '%s%s%s',
                    $metricData['data'],
                    AttributeColumnInfoExtractor::UNIT_SEPARATOR,
                    $metricData['unit']
                )
            );
        }

        return $resultRow;
    }

    /**
     * Collect price data exploded in different columns
     *
     * @param array  $collectedPrices
     * @param array  $attributeInfos
     * @param string $fieldValue
     *
     * @return array collected metrics
     */
    protected function collectPriceData(array $collectedPrices, array $attributeInfos, $fieldValue)
    {
        $cleanField = $this->getCleanFieldName($attributeInfos);
        if (null !== $attributeInfos['price_currency'] && trim($fieldValue) !== '') {
            $collectedPrices[$cleanField] = $collectedPrices[$cleanField] ?? [];

            $collectedPrices[$cleanField][] = sprintf(
                '%s%s%s',
                $fieldValue,
                AttributeColumnInfoExtractor::UNIT_SEPARATOR,
                $attributeInfos['price_currency']
            );
        } else {
            $collectedPrices[$cleanField] = explode(AttributeColumnInfoExtractor::ARRAY_SEPARATOR, $fieldValue);
        }

        return $collectedPrices;
    }

    /**
     * Merge collected price in result rows
     *
     * @param array $resultRow
     * @param array $collectedPrices
     *
     * @return array
     */
    protected function mergePriceData(array $resultRow, array $collectedPrices)
    {
        foreach ($collectedPrices as $fieldName => $prices) {
            $resultRow[$fieldName] = implode(AttributeColumnInfoExtractor::ARRAY_SEPARATOR, $prices);
        }

        return $resultRow;
    }
}
