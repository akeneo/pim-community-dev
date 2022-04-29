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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOption;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class TableValueFactory implements ValueFactory
{
    public function __construct(
        private TableConfigurationRepository $tableConfigurationRepository,
        private SelectOptionCollectionRepository $selectOptionCollectionRepository,
        private GetExistingRecordCodes $getExistingRecordCodes
    ) {
    }

    public function createByCheckingData(
        Attribute $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data
    ): ValueInterface {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->code(), static::class, $data);
        }
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw InvalidPropertyTypeException::arrayOfArraysExpected($attribute->code(), static::class, $data);
            }

            foreach ($row as $cell) {
                if (null === $cell || '' === $cell) {
                    continue;
                }

                if (!is_scalar($cell) && !\is_array($cell)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $attribute->code(),
                        'The cell value must be a text string, a number, a boolean or an array.',
                        static::class,
                        $data
                    );
                }
            }
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function createWithoutCheckingData(
        Attribute $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data
    ): ValueInterface {
        $data = $this->replaceColumnCodesByIds($attribute, $data);
        $data = $this->removeDuplicateOnFirstColumn($attribute, $data);
        $data = $this->sanitizeSelectOptionCode($attribute, $data);
        $data = $this->sanitizeReferenceEntityCode($attribute, $data);
        $table = Table::fromNormalized($data);
        if ($attribute->isLocalizableAndScopable()) {
            return TableValue::scopableLocalizableValue($attribute->code(), $table, $channelCode, $localeCode);
        }
        if ($attribute->isScopable()) {
            return TableValue::scopableValue($attribute->code(), $table, $channelCode);
        }
        if ($attribute->isLocalizable()) {
            return TableValue::localizableValue($attribute->code(), $table, $localeCode);
        }

        return TableValue::value($attribute->code(), $table);
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return array<int, array<string, mixed>>
     */
    private function removeDuplicateOnFirstColumn(Attribute $attribute, array $data): array
    {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->code());
        $firstColumnId = \strtolower($tableConfiguration->getFirstColumnId()->asString());

        $foundOptionCodes = [];
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnId => $value) {
                if ($firstColumnId === \strtolower((string) $columnId) && \is_scalar($value)) {
                    $optionCode = \strtolower((string) $value);
                    if (array_key_exists($optionCode, $foundOptionCodes)) {
                        unset($data[$foundOptionCodes[$optionCode]]);
                    }
                    $foundOptionCodes[$optionCode] = $rowIndex;
                }
            }
        }

        return $data;
    }

    /**
     * This method finds the real select option codes to have the good cases (do nothing if the option is not found).
     *
     * @param array<int, array<string, mixed>> $data
     * @return array<int, array<string, mixed>>
     */
    private function sanitizeSelectOptionCode(Attribute $attribute, array $data): array
    {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->code());

        $indexedSelectColumnIds = [];
        foreach ($tableConfiguration->getSelectColumns() as $selectColumn) {
            $indexedSelectColumnIds[\strtolower($selectColumn->id()->asString())] = $selectColumn;
        }

        if ([] === $indexedSelectColumnIds) {
            return $data;
        }

        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnId => $value) {
                $selectColumn = $indexedSelectColumnIds[\strtolower($columnId)] ?? null;
                if (null !== $selectColumn && \is_scalar($value)) {
                    $option = $this->getOption($attribute->code(), $selectColumn->code(), (string) $value);
                    if (null !== $option) {
                        $data[$rowIndex][$columnId] = $option->code()->asString();
                    }
                }
            }
        }

        return $data;
    }

    /**
     * This method finds the real record codes to have the good cases (do nothing if the record is not found).
     *
     * @param array<int, array<string, mixed>> $data
     * @return array<int, array<string, mixed>>
     */
    private function sanitizeReferenceEntityCode(Attribute $attribute, array $data): array
    {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->code());

        $indexedReferenceEntityColumnIds = [];
        foreach ($tableConfiguration->getReferenceEntityColumns() as $referenceEntityColumn) {
            $indexedReferenceEntityColumnIds[\strtolower($referenceEntityColumn->id()->asString())] = $referenceEntityColumn;
        }

        if ([] === $indexedReferenceEntityColumnIds) {
            return $data;
        }

        $recordCodesToCheck = [];
        foreach ($data as $row) {
            foreach ($row as $columnId => $value) {
                $referenceEntityColumn = $indexedReferenceEntityColumnIds[\strtolower($columnId)] ?? null;
                if (null !== $referenceEntityColumn) {
                    $recordCodesToCheck[$referenceEntityColumn->referenceEntityIdentifier()->asString()][] = (string) $value;
                }
            }
        }

        if ([] === $recordCodesToCheck) {
            return $data;
        }

        $existingRecordCodes = $this->getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            $recordCodesToCheck
        );

        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnId => $value) {
                $referenceEntityColumn = $indexedReferenceEntityColumnIds[\strtolower($columnId)] ?? null;
                if (null === $referenceEntityColumn) {
                    continue;
                }

                $filteredRecordCodes = $existingRecordCodes[$referenceEntityColumn->referenceEntityIdentifier()->asString()] ?? [];
                $valueIndex = array_search(
                    \strtolower((string) $value),
                    \array_map('strtolower', $filteredRecordCodes)
                ) ?? null;

                if (\is_integer($valueIndex) && $valueIndex >= 0) {
                    $data[$rowIndex][$columnId] = $filteredRecordCodes[$valueIndex];
                }
            }
        }

        return $data;
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::TABLE;
    }

    /**
     * It removes values if the column is not found
     */
    private function replaceColumnCodesByIds(Attribute $attribute, $data): array
    {
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->code());
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnIdentifier => $value) {
                $id = $this->getColumnId((string) $columnIdentifier, $tableConfiguration);
                unset($data[$rowIndex][$columnIdentifier]);
                $data[$rowIndex][$id] = $value;
            }
        }

        return $data;
    }

    private function getColumnId(string $columnIdentifier, TableConfiguration $tableConfiguration): string
    {
        if ($this->columnIdentifierIsAnId($columnIdentifier)) {
            return $columnIdentifier;
        }

        $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString($columnIdentifier));
        if (null === $column) {
            return $columnIdentifier;
        }

        return $column->id()->asString();
    }

    private function columnIdentifierIsAnId(string $columnIdentifier): bool
    {
        return false !== strpos($columnIdentifier, '-');
    }

    private function getOption(string $attributeCode, ColumnCode $columnCode, string $optionCode): ?SelectOption
    {
        $selectOptionCollection = $this->selectOptionCollectionRepository->getByColumn($attributeCode, $columnCode);

        return $selectOptionCollection->getByCode($optionCode);
    }
}
