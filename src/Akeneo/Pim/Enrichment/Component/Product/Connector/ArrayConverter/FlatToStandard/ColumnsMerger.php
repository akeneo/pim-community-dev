<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use Ramsey\Uuid\Uuid;

/**
 * Merge columns for single value that can be provided in many columns like prices and metric.
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
    public function merge(array $row, array $options)
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
                    // For XLSX import, the value could be already converted to a DateTime object (cf PIM-10167)
                    if ($fieldValue instanceof \DateTimeInterface) {
                        throw new BusinessArrayConversionException("Can not convert cell {$fieldName} with date format to attribute of type date", 'pim_import_export.notification.import.warnings.xlsx_cell_date_conversion_error', ['{cellName}' => $fieldName, '{attributeType}' => 'price']);
                    }

                    $collectedPrices = $this->collectPriceData($collectedPrices, $attributeInfos, $fieldValue, $options);
                } else {
                    $resultRow[$fieldName] = $fieldValue;
                }
            } else {
                if (in_array($fieldName, $this->associationColumnResolver->resolveQuantifiedQuantityAssociationColumns())) {
                    $collectedQuantifiedAssociations = $this->collectQuantifiedQuantityAssociationData($collectedQuantifiedAssociations, $fieldName, $fieldValue);
                } elseif (in_array($fieldName, $this->associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns())) {
                    $collectedQuantifiedAssociations = $this->collectQuantifiedIdentifierAssociationData($collectedQuantifiedAssociations, $fieldName, $fieldValue);
                } else {
                    $resultRow[$fieldName] = $fieldValue;
                }
            }
        }

        $resultRow = $this->mergeMetricData($resultRow, $collectedMetrics, $options);
        $resultRow = $this->mergePriceData($resultRow, $collectedPrices);
        $resultRow = $this->mergeQuantifiedAssociationData($resultRow, $collectedQuantifiedAssociations);

        return $resultRow;
    }

    /**
     * Returns a clean field name with code, locale and scope (without unit, currency, etc in the field).
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
     * Collect metric data exploded in different columns.
     *
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
            if (is_string($fieldValue)) {
                $collectedMetrics[$cleanField]['data'] = trim($fieldValue);
            } else {
                $collectedMetrics[$cleanField]['data'] = $fieldValue;
            }
        }

        return $collectedMetrics;
    }

    /**
     * Merge collected metric in result rows.
     *
     * @return array
     */
    protected function mergeMetricData(array $resultRow, array $collectedMetrics, array $options): array
    {
        foreach ($collectedMetrics as $fieldName => $metricData) {
            $metricValue = $metricData['data'];

            if (is_float($metricValue)) {
                $metricValue = number_format(
                    $metricValue,
                    decimals: MeasureConverter::SCALE,
                    decimal_separator: $options['decimal_separator'] ?? '.',
                    thousands_separator: ''
                );
            }

            $resultRow[$fieldName] = trim(
                sprintf(
                    '%s%s%s',
                    $metricValue,
                    AttributeColumnInfoExtractor::UNIT_SEPARATOR,
                    $metricData['unit']
                )
            );
        }

        return $resultRow;
    }

    /**
     * Collect price data exploded in different columns.
     *
     * @param string $fieldValue
     *
     * @return array collected metrics
     */
    protected function collectPriceData(array $collectedPrices, array $attributeInfos, mixed $fieldValue, array $options)
    {
        $cleanField = $this->getCleanFieldName($attributeInfos);
        if (null !== $attributeInfos['price_currency']) {
            $collectedPrices[$cleanField] = $collectedPrices[$cleanField] ?? [];
            if ('' === trim($fieldValue)) {
                return $collectedPrices;
            }

            if (is_float($fieldValue)) {
                $fieldValue = str_replace('.', $options['decimal_separator'] ?? '.', $fieldValue);
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

    private function collectQuantifiedQuantityAssociationData(array $collectedQuantifiedAssociations, string $fieldName, $fieldValue): array
    {
        list($associationTypeCode, $productType) = explode('-', $fieldName);
        if (!isset($collectedQuantifiedAssociations[$associationTypeCode])) {
            $collectedQuantifiedAssociations[$associationTypeCode] = ['products' => [], 'product_models' => []];
        }

        if (empty($fieldValue)) {
            return $collectedQuantifiedAssociations;
        }

        $collectedQuantifiedAssociations[$associationTypeCode][$productType] = array_merge(
            $collectedQuantifiedAssociations[$associationTypeCode][$productType] ?? [],
            ['quantities' => explode(ProductAssociation::QUANTITY_SEPARATOR, $fieldValue)]
        );

        return $collectedQuantifiedAssociations;
    }

    private function collectQuantifiedIdentifierAssociationData(array $collectedQuantifiedAssociations, string $fieldName, $fieldValue): array
    {
        list($associationTypeCode, $productType) = explode('-', $fieldName);
        if (!isset($collectedQuantifiedAssociations[$associationTypeCode])) {
            $collectedQuantifiedAssociations[$associationTypeCode] = ['products' => [], 'product_models' => []];
        }

        if (empty($fieldValue)) {
            return $collectedQuantifiedAssociations;
        }

        $values = explode(ProductAssociation::IDENTIFIER_SEPARATOR, $fieldValue);
        $isUuids = \count(\array_filter($values, fn ($value) => !Uuid::isValid($value))) === 0;

        if ($isUuids) {
            $newQuantifiedAssociations = ['uuids' => $values];
        } else {
            $newQuantifiedAssociations = ['identifiers' => $values];
        }
        $collectedQuantifiedAssociations[$associationTypeCode][$productType] = array_merge(
            $collectedQuantifiedAssociations[$associationTypeCode][$productType] ?? [],
            $newQuantifiedAssociations
        );

        return $collectedQuantifiedAssociations;
    }

    /**
     * Merge collected price in result rows.
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

    private function mergeQuantifiedAssociationData(array $resultRow, array $collectedQuantifiedAssociations): array
    {
        foreach ($collectedQuantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            foreach (['products', 'product_models'] as $entityType) {
                if (empty($quantifiedAssociation[$entityType])) {
                    $resultRow[sprintf('%s%s%s', $associationTypeCode, AttributeColumnInfoExtractor::FIELD_SEPARATOR, $entityType)] = [];

                    continue;
                }

                if (!array_key_exists('quantities', $quantifiedAssociation[$entityType])) {
                    throw new \LogicException(sprintf('A "%s-%s" column is missing for quantified association', $associationTypeCode, $entityType.$this->associationColumnResolver::QUANTITY_SUFFIX));
                }

                $isUuids = \array_key_exists('uuids', $quantifiedAssociation[$entityType]);
                $uuidsOrIdentifiers = $isUuids ? $quantifiedAssociation[$entityType]['uuids'] : $quantifiedAssociation[$entityType]['identifiers'];

                if (count($uuidsOrIdentifiers) !== count($quantifiedAssociation[$entityType]['quantities'])) {
                    throw new \LogicException('Inconsistency detected: the count of uuids and quantities is not the same');
                }

                $resultRow[sprintf('%s%s%s', $associationTypeCode, AttributeColumnInfoExtractor::FIELD_SEPARATOR, $entityType)] =
                array_map(
                    function ($uuid, $quantity) use ($isUuids) {
                        if ($isUuids) {
                            return ['uuid' => $uuid, 'quantity' => (int)$quantity];
                        } else {
                            return ['identifier' => $uuid, 'quantity' => (int)$quantity];
                        }
                    },
                    $uuidsOrIdentifiers,
                    $quantifiedAssociation[$entityType]['quantities']
                );
            }
        }

        return $resultRow;
    }
}
