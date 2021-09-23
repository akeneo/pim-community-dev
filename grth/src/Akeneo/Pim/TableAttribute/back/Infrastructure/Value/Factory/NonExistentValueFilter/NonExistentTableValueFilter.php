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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\NonExistentValueFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;

/**
 * Filters table values:
 * - non-existing column id => the cell is removed
 * - non-existing options:
 *      - remove the cell if it's not the first column
 *      - remove the whole row if it's the first column.
 */
class NonExistentTableValueFilter implements NonExistentValuesFilter
{
    private TableConfigurationRepository $tableConfigurationRepository;
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;

    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $selectOptionCollectionRepository
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $filteredValues = [];
        $tableValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::TABLE);

        foreach ($tableValues as $attributeCode => $productValueCollections) {
            try {
                $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attributeCode);
            } catch (TableConfigurationNotFoundException $e) {
                $filteredValues[AttributeTypes::TABLE][$attributeCode] = [];

                continue;
            }
            $existingColumnIds = \array_map(
                fn (ColumnId $columnId): string => $columnId->asString(),
                $tableConfiguration->columnIds()
            );
            foreach ($productValueCollections as $valueCollection) {
                $filteredValues[AttributeTypes::TABLE][$attributeCode][] = $this->filterValues(
                    $valueCollection,
                    $attributeCode,
                    $tableConfiguration,
                    $existingColumnIds
                );
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function filterValues(
        array $rawValuesPerChannelAndLocale,
        string $attributeCode,
        TableConfiguration $tableConfiguration,
        array $existingColumnIds
    ): array {
        $filteredRawValues = [];
        foreach ($rawValuesPerChannelAndLocale['values'] as $channelCode => $rawValuesPerLocale) {
            foreach ($rawValuesPerLocale as $localeCode => $rawTableValue) {
                $filteredRawTableValue = $this->filterNonExistingColumnIds($rawTableValue, $existingColumnIds);
                $filteredRawTableValue = $this->filterNonExistingOption($filteredRawTableValue, $attributeCode, $tableConfiguration);

                $filteredRawValues[$channelCode][$localeCode] = $filteredRawTableValue;
            }
        }

        return [
            'identifier' => $rawValuesPerChannelAndLocale['identifier'],
            'values' => $filteredRawValues,
        ];
    }

    private function filterNonExistingColumnIds(array $rawTableValue, array $existingColumnIds): array
    {
        $filteredRawTableValue = [];
        foreach ($rawTableValue as $cells) {
            $filteredCells = \array_intersect_key(
                $cells,
                \array_flip($existingColumnIds)
            );
            if ([] !== $filteredCells) {
                $filteredRawTableValue[] = $filteredCells;
            }
        }

        return $filteredRawTableValue;
    }

    private function filterNonExistingOption(
        array $rawTableValue,
        string $attributeCode,
        TableConfiguration $tableConfiguration
    ): array {
        $firstColumnId = $tableConfiguration->getFirstColumnId()->asString();

        $selectColumnIds = [];
        foreach ($tableConfiguration->getSelectColumns() as $selectColumn) {
            $selectColumnIds[\strtolower($selectColumn->id()->asString())] = $selectColumn;
        }

        foreach ($rawTableValue as $rowIndex => $row) {
            foreach ($row as $columnId => $value) {
                $foundColumn = $selectColumnIds[\strtolower($columnId)] ?? null;
                if (null !== $foundColumn
                    && !$this->optionExists($attributeCode, $foundColumn->code(), $value)
                ) {
                    unset($rawTableValue[$rowIndex][$columnId]);
                }
            }

            if (!array_key_exists($firstColumnId, $rawTableValue[$rowIndex])) {
                unset($rawTableValue[$rowIndex]);
            }
        }

        return array_values($rawTableValue);
    }

    private function optionExists(string $attributeCode, ColumnCode $columnCode, string $option): bool
    {
        $selectOptionCollection = $this->selectOptionCollectionRepository->getByColumn($attributeCode, $columnCode);

        return null !== $selectOptionCollection->getByCode($option);
    }
}
