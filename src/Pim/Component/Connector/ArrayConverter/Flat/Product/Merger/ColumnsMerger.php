<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Merger;

use Pim\Component\Connector\ArrayConverter\Flat\ProductAttributeFieldExtractor;

/**
 * Merge columns for single value that can be provided in many columns like prices and metric
 *
 * These two values supports two different formats we, standardize here to the one column format
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
    /** @var ProductAttributeFieldExtractor */
    protected $fieldExtractor;

    /**
     * @param ProductAttributeFieldExtractor $fieldExtractor
     */
    public function __construct(ProductAttributeFieldExtractor $fieldExtractor)
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
        $resultRows = [];
        $collectedMetrics = [];
        $collectedPrices = [];
        foreach ($row as $fieldName => $fieldValue) {
            $attributeInfos = $this->fieldExtractor->extractAttributeFieldNameInfos($fieldName);
            if (null !== $attributeInfos) {
                $attribute = $attributeInfos['attribute'];
                if ('metric' === $attribute->getBackendType()) {
                    $collectedMetrics = $this->collectMetricData($collectedMetrics, $attributeInfos, $fieldValue);
                } elseif ('prices' === $attribute->getBackendType()) {
                    $collectedPrices = $this->collectPriceData($collectedPrices, $attributeInfos, $fieldValue);
                } else {
                    $resultRows[$fieldName] = $fieldValue;
                }
            } else {
                $resultRows[$fieldName] = $fieldValue;
            }
        }

        $resultRows = $this->mergeMetricData($resultRows, $collectedMetrics);
        $resultRows = $this->mergePriceData($resultRows, $collectedPrices);

        return $resultRows;
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
        $cleanField .= ($attributeInfos['locale_code'] === null) ?
            '' : ProductAttributeFieldExtractor::FIELD_SEPARATOR.$attributeInfos['locale_code'];
        $cleanField .= ($attributeInfos['scope_code'] === null) ?
            '' : ProductAttributeFieldExtractor::FIELD_SEPARATOR.$attributeInfos['scope_code'];

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
     * @param array $resultRows
     * @param array $collectedMetrics
     *
     * @return array
     */
    protected function mergeMetricData(array $resultRows, array $collectedMetrics)
    {
        foreach ($collectedMetrics as $fieldName => $metricData) {
            $resultRows[$fieldName] = trim(
                sprintf(
                    '%s%s%s',
                    $metricData['data'],
                    ProductAttributeFieldExtractor::UNIT_SEPARATOR,
                    $metricData['unit']
                )
            );
        }

        return $resultRows;
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
        if (NULL !== $attributeInfos['price_currency']) {
            if (!in_array($cleanField, array_keys($collectedPrices))) {
                $collectedMetrics[$cleanField] = [];
            }
            $collectedPrices[$cleanField][] = sprintf(
                '%s%s%s',
                $fieldValue,
                ProductAttributeFieldExtractor::UNIT_SEPARATOR,
                $attributeInfos['price_currency']
            );
        } else {
            $collectedPrices[$cleanField] = explode(ProductAttributeFieldExtractor::ARRAY_SEPARATOR, $fieldValue);
        }

        return $collectedPrices;
    }

    /**
     * Merge collected price in result rows
     *
     * @param array $resultRows
     * @param array $collectedPrices
     *
     * @return array
     */
    protected function mergePriceData(array $resultRows, array $collectedPrices)
    {
        foreach ($collectedPrices as $fieldName => $prices) {
            $resultRows[$fieldName] = implode(ProductAttributeFieldExtractor::ARRAY_SEPARATOR, $prices);
        }

        return $resultRows;
    }
}