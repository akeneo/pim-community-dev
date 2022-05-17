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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOption;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;

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
    private GetExistingRecordCodes $getExistingRecordCodes;

    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        GetExistingRecordCodes $getExistingRecordCodes
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
        $this->getExistingRecordCodes = $getExistingRecordCodes;
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
                $filteredRawTableValue = $this->filterNonExistingRecords($filteredRawTableValue, $tableConfiguration);

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
                if (null === $foundColumn) {
                    continue;
                }

                $option = $this->getOption($attributeCode, $foundColumn->code(), $value);
                if (null === $option) {
                    unset($rawTableValue[$rowIndex][$columnId]);
                } else {
                    $rawTableValue[$rowIndex][$columnId] = $option->code()->asString();
                }
            }

            if (!array_key_exists($firstColumnId, $rawTableValue[$rowIndex])) {
                unset($rawTableValue[$rowIndex]);
            }
        }

        return array_values($rawTableValue);
    }

    private function filterNonExistingRecords(
        array $rawTableValue,
        TableConfiguration $tableConfiguration
    ): array {
        $firstColumnId = $tableConfiguration->getFirstColumnId()->asString();

        $referenceEntityColumnObject = [];
        foreach ($tableConfiguration->getReferenceEntityColumns() as $referenceEntityColumn) {
            /** @var ReferenceEntityColumn $referenceEntityColumn */
            $referenceEntityColumnObject[\strtolower($referenceEntityColumn->id()->asString())] = $referenceEntityColumn;
        }

        $recordCodes = [];
        foreach ($rawTableValue as $row) {
            foreach ($row as $columnId => $value) {
                $foundReferenceEntityColumn = $referenceEntityColumnObject[\strtolower($columnId)] ?? null;
                if (null === $foundReferenceEntityColumn) {
                    continue;
                }
                $recordCodes[$foundReferenceEntityColumn->referenceEntityIdentifier()->asString()][] = $value;
            }
        }

        $existingRecordCodes = [];
        if (!empty($recordCodes)) {
            $existingRecordCodes = $this->getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes($recordCodes);
        }

        foreach ($rawTableValue as $rowIndex => $row) {
            foreach ($row as $columnId => $value) {
                $foundReferenceEntityColumn = $referenceEntityColumnObject[\strtolower($columnId)] ?? null;
                if (null === $foundReferenceEntityColumn) {
                    continue;
                }

                $filteredRecordCodes = $existingRecordCodes[\strtolower($foundReferenceEntityColumn->referenceEntityIdentifier()->asString())] ?? [];
                $valueIndex = array_search(
                    strtolower($value),
                    array_map('strtolower', $filteredRecordCodes)
                ) ?? null;

                if (is_integer($valueIndex) && $valueIndex >= 0) {
                    $rawTableValue[$rowIndex][$columnId] = $filteredRecordCodes[$valueIndex];
                } else {
                    unset($rawTableValue[$rowIndex][$columnId]);
                }
            }

            if (!array_key_exists($firstColumnId, $rawTableValue[$rowIndex])) {
                unset($rawTableValue[$rowIndex]);
            }
        }

        return array_values($rawTableValue);
    }

    private function getOption(string $attributeCode, ColumnCode $columnCode, string $option): ?SelectOption
    {
        $selectOptionCollection = $this->selectOptionCollectionRepository->getByColumn($attributeCode, $columnCode);

        return $selectOptionCollection->getByCode($option);
    }
}
