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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Webmozart\Assert\Assert;

final class TableConfiguration
{
    /**
     * @var array<string, ColumnDefinition>
     * ColumnDefinition indexed by their column ids.
     * Be careful if you update this parameter: the order is very important to know which is the first column,
     * so never do some sort on it for instance.
     */
    private array $columnDefinitions;

    /**
     * @param array<int, ColumnDefinition> $columnDefinitions
     */
    private function __construct(array $columnDefinitions)
    {
        foreach ($columnDefinitions as $columnDefinition) {
            $this->columnDefinitions[$columnDefinition->id()->asString()] = $columnDefinition;
        }
    }

    /**
     * @param array<int, ColumnDefinition> $columnDefinitions
     * @internal
     */
    public static function fromColumnDefinitions(array $columnDefinitions): self
    {
        $columnDefinitions = array_values($columnDefinitions);
        Assert::allIsInstanceOf($columnDefinitions, ColumnDefinition::class);
        Assert::minCount($columnDefinitions, 2);
        Assert::isInstanceOfAny($columnDefinitions[0], [SelectColumn::class, ReferenceEntityColumn::class], 'The first column has an invalid type');
        Assert::true($columnDefinitions[0]->isRequiredForCompleteness()->asBoolean());

        $codes = \array_map(
            fn (ColumnDefinition $definition): string => strtolower($definition->code()->asString()),
            $columnDefinitions
        );
        Assert::uniqueValues($codes, 'The column codes are not unique');
        $ids = \array_map(
            fn (ColumnDefinition $definition): string => strtolower($definition->id()->asString()),
            $columnDefinitions
        );
        Assert::uniqueValues($ids, 'The column ids are not unique');

        return new self($columnDefinitions);
    }

    /**
     * @return array<int, array>
     */
    public function normalize(): array
    {
        return \array_values(\array_map(
            fn (ColumnDefinition $columnDefinition): array => $columnDefinition->normalize(),
            $this->columnDefinitions
        ));
    }

    /**
     * @return ColumnId[]
     */
    public function columnIds(): array
    {
        return \array_values(\array_map(
            fn (ColumnDefinition $column): ColumnId => $column->id(),
            $this->columnDefinitions
        ));
    }

    /**
     * @return ColumnCode[]
     */
    public function columnCodes(): array
    {
        return \array_values(\array_map(
            fn (ColumnDefinition $column): ColumnCode => $column->code(),
            $this->columnDefinitions
        ));
    }

    /**
     * @return ColumnDefinition[]
     */
    public function requiredColumns(): array
    {
        return \array_filter(
            $this->columnDefinitions,
            fn (ColumnDefinition $columnDefinition): bool => $columnDefinition->isRequiredForCompleteness()->asBoolean()
        );
    }

    public function getFirstColumnId(): ColumnId
    {
        reset($this->columnDefinitions);
        $firstColumn = current($this->columnDefinitions);
        Assert::implementsInterface($firstColumn, ColumnDefinition::class);

        return $firstColumn->id();
    }

    public function getFirstColumnCode(): ColumnCode
    {
        reset($this->columnDefinitions);
        $firstColumn = current($this->columnDefinitions);
        Assert::implementsInterface($firstColumn, ColumnDefinition::class);

        return $firstColumn->code();
    }

    /**
     * @return SelectColumn[]
     */
    public function getSelectColumns(): array
    {
        return \array_values(\array_filter(
            $this->columnDefinitions,
            fn (ColumnDefinition $columnDefinition): bool => $columnDefinition instanceof SelectColumn
        ));
    }

    /**
     * @return ReferenceEntityColumn[]
     */
    public function getReferenceEntityColumns(): array
    {
        return \array_values(\array_filter(
            $this->columnDefinitions,
            fn (ColumnDefinition $columnDefinition): bool => $columnDefinition instanceof ReferenceEntityColumn
        ));
    }

    public function getValidations(ColumnCode $columnCode): ?ValidationCollection
    {
        foreach ($this->columnDefinitions as $columnDefinition) {
            if ($columnDefinition->code()->equals($columnCode)) {
                return $columnDefinition->validations();
            }
        }

        return null;
    }

    public function getColumn(ColumnId $columnId): ?ColumnDefinition
    {
        return $this->columnDefinitions[$columnId->asString()] ?? null;
    }

    public function getColumnByCode(ColumnCode $columnCode): ?ColumnDefinition
    {
        foreach ($this->columnDefinitions as $columnDefinition) {
            if ($columnDefinition->code()->equals($columnCode)) {
                return $columnDefinition;
            }
        }

        return null;
    }

    public function getColumnFromStringId(string $columnId): ?ColumnDefinition
    {
        return $this->columnDefinitions[$columnId] ?? null;
    }
}
