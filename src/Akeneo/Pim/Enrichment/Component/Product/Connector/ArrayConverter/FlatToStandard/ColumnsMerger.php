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

    /** @var AssociationColumnsResolver */
    protected $associationColumnResolver;

    /**
     * @param AttributeColumnInfoExtractor $fieldExtractor
     * @param AssociationColumnsResolver   $associationColumnResolver
     */
    public function __construct(AttributeColumnInfoExtractor $fieldExtractor, AssociationColumnsResolver $associationColumnResolver)
    {
        $this->fieldExtractor = $fieldExtractor;
        $this->associationColumnResolver = $associationColumnResolver;
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
        $collectedQuantifiedAssociations = [];
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
                if (in_array($fieldName, $this->associationColumnResolver->resolveQuantifiedQuantityAssociationColumns())) {
                    $collectedQuantifiedAssociations = $this->collectQuantifiedQuantityAssociationData($collectedQuantifiedAssociations, $fieldName, $fieldValue);
                } elseif (in_array($fieldName, $this->associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns())) {
                    $collectedQuantifiedAssociations = $this->collectQuantifiedIdentifierAssociationData($collectedQuantifiedAssociations, $fieldName, $fieldValue);
                }

                $resultRow[$fieldName] = $fieldValue;
            }
        }

        $resultRow = $this->mergeMetricData($resultRow, $collectedMetrics);
        $resultRow = $this->mergePriceData($resultRow, $collectedPrices);
        $resultRow = $this->mergeQuantifiedAssociationData($resultRow, $collectedQuantifiedAssociations);

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
        if (null !== $attributeInfos['price_currency']) {
            $collectedPrices[$cleanField] = $collectedPrices[$cleanField] ?? [];
            if (trim($fieldValue) === '') {
                return $collectedPrices;
            }
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

    private function collectQuantifiedQuantityAssociationData(array $collectedQuantifiedAssociations, string $fieldName, $fieldValue)
    {
        list($associationTypeCode, $productType) = explode('-', $fieldName);
        if (!isset($collectedQuantifiedAssociations[$associationTypeCode])) {
            $collectedQuantifiedAssociations[$associationTypeCode] = ['products' => [], 'product_models' => []];
        }

        if (empty($fieldValue)) {
            return $collectedQuantifiedAssociations;
        }

        $quantities = explode(ProductAssociation::QUANTITY_SEPARATOR, $fieldValue);
        $collectedQuantifiedAssociations[$associationTypeCode][$productType] = array_reduce(
            array_keys($quantities),
            function ($result, $index) use ($quantities) {
                $result[$index] = array_merge(isset($result[$index]) ? $result[$index] : [], ['quantity' => (int) $quantities[$index]]);

                return $result;
            },
            $collectedQuantifiedAssociations[$associationTypeCode][$productType]
        );

        return $collectedQuantifiedAssociations;
    }

    private function collectQuantifiedIdentifierAssociationData(array $collectedQuantifiedAssociations, string $fieldName, $fieldValue)
    {
        list($associationTypeCode, $productType) = explode('-', $fieldName);
        if (!isset($collectedQuantifiedAssociations[$associationTypeCode])) {
            $collectedQuantifiedAssociations[$associationTypeCode] = ['products' => [], 'product_models' => []];
        }

        if (empty($fieldValue)) {
            return $collectedQuantifiedAssociations;
        }

        $identifiers = explode(ProductAssociation::IDENTIFIER_SEPARATOR, $fieldValue);
        $collectedQuantifiedAssociations[$associationTypeCode][$productType] = array_reduce(
            array_keys($identifiers),
            function ($result, $index) use ($identifiers) {
                $result[$index] = array_merge(isset($result[$index]) ? $result[$index] : [], ['identifier' => $identifiers[$index]]);

                return $result;
            },
            $collectedQuantifiedAssociations[$associationTypeCode][$productType]
        );

        return $collectedQuantifiedAssociations;
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

    /**
     * Merge quantified associations in result rows
     *
     * @param array $resultRow
     * @param array $collectedQuantifiedAssociations
     *
     * @return array
     */
    protected function mergeQuantifiedAssociationData(array $resultRow, array $collectedQuantifiedAssociations)
    {
        foreach ($collectedQuantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            $resultRow[sprintf('%s%s%s', $associationTypeCode, AttributeColumnInfoExtractor::FIELD_SEPARATOR, 'products')] = $quantifiedAssociation['products'];
            $resultRow[sprintf('%s%s%s', $associationTypeCode, AttributeColumnInfoExtractor::FIELD_SEPARATOR, 'product_models')] = $quantifiedAssociation['product_models'];
        }

        return $resultRow;
    }
}
