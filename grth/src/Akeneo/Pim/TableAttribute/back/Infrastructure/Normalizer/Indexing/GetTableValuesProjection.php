<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\AclMeasureConverter;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Webmozart\Assert\Assert;

class GetTableValuesProjection implements GetAdditionalPropertiesForProductProjectionInterface, GetAdditionalPropertiesForProductModelProjectionInterface
{
    public function __construct(
        private TableConfigurationRepository $tableConfigurationRepository,
        private AclMeasureConverter $measureConverter
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function fromProductUuids(array $productUuids, array $context = []): array
    {
        Assert::keyExists($context, 'value_collections');

        return $this->fromMultipleValueCollection($context['value_collections']);
    }

    /**
     * {@inheritDoc}
     */
    public function fromProductModelCodes(array $productModelCodes, array $context = []): array
    {
        Assert::keyExists($context, 'value_collections');

        return $this->fromMultipleValueCollection($context['value_collections']);
    }

    /**
     * @param array<string, ReadValueCollection> $valueCollectionIndexedByUuid
     * @return array<string, array<mixed>>
     *
     * Create table value projection from value collections.
     *
     * The resulted projections are:
     *  {
     *      "uuid1": {
     *          "table_values": {
     *              "nutrition": [
     *                  {
     *                      "channel": "ecommerce",
     *                      "locale": "en_US",
     *                      "row": "ingredient",
     *                      "column": "ingredient",
     *                      "value-select": "salt",
     *                      "is_column_complete": true
     *                  },
     *                  ...
     *              ]
     *          }
     *      }
     *      "uuid2": {
     *          "table_values": {
     *              "packaging": [
     *                  {
     *                      "row": "parcel_1",
     *                      "column": "parcel",
     *                      "value-select": "parcel_1",
     *                      "is_column_complete": true
     *                  },
     *                  ...
     *              ]
     *          }
     *      }
     *  }
     */
    private function fromMultipleValueCollection(array $valueCollectionIndexedByUuid): array
    {
        if ([] === $valueCollectionIndexedByUuid) {
            return [];
        }

        $results = [];
        foreach ($valueCollectionIndexedByUuid as $uuid => $valueCollection) {
            foreach ($valueCollection as $value) {
                if (!$value instanceof TableValue) {
                    continue;
                }
                $attributeCode = $value->getAttributeCode();
                $results[$uuid]['table_values'][$attributeCode] = array_merge(
                    $results[$uuid]['table_values'][$attributeCode] ?? [],
                    $this->convertValueToArrayProjection($value)
                );
            }
        }

        return $results;
    }

    private function convertValueToArrayProjection(TableValue $tableValue): array
    {
        $valueProjection = [];

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableValue->getAttributeCode());
        $stringFirstColumnId = $tableConfiguration->getFirstColumnId()->asString();

        $table = $tableValue->getData();
        $rowCount = \count($table);
        $filledCellsCountByColumn = $this->getFilledCellsCountByColumn($table);

        // Normalize the table at the beginning is a little faster
        foreach ($table->normalize() as $row) {
            foreach ($row as $columnId => $cellValue) {
                $column = $tableConfiguration->getColumnFromStringId($columnId);
                $value = $column instanceof MeasurementColumn
                    ? $this->measureConverter->convertAmountInStandardUnit(
                        $column->measurementFamilyCode(),
                        (string) $cellValue['amount'],
                        $cellValue['unit']
                    )
                    : $cellValue
                ;
                $normalizedCell = [
                    'row' => $row[$stringFirstColumnId],
                    'column' => $column->code()->asString(),
                    \sprintf('value-%s', $column->dataType()->asString()) => $value,
                    'is_column_complete' => $filledCellsCountByColumn[$columnId] === $rowCount,
                ];
                if (null !== $tableValue->getLocaleCode()) {
                    $normalizedCell['locale'] = $tableValue->getLocaleCode();
                }
                if (null !== $tableValue->getScopeCode()) {
                    $normalizedCell['channel'] = $tableValue->getScopeCode();
                }
                $valueProjection[] = $normalizedCell;
            }
        }

        return $valueProjection;
    }

    private function getFilledCellsCountByColumn(Table $table): array
    {
        $filledCellsCountByColumn = [];
        foreach ($table as $row) {
            foreach ($row as $columnId => $cellValue) {
                $filledCellsCountByColumn[$columnId] = 1 + ($filledCellsCountByColumn[$columnId] ?? 0);
            }
        }

        return $filledCellsCountByColumn;
    }
}
