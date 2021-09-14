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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Webmozart\Assert\Assert;

final class TableConfiguration
{
    /** @var array<int, ColumnDefinition> */
    private array $columnDefinitions;

    /**
     * @param array<int, ColumnDefinition> $columnDefinitions
     */
    private function __construct(array $columnDefinitions)
    {
        $this->columnDefinitions = $columnDefinitions;
    }

    /**
     * @param array<int, ColumnDefinition> $columnDefinitions
     */
    public static function fromColumnDefinitions(array $columnDefinitions): self
    {
        $columnDefinitions = array_values($columnDefinitions);
        Assert::allIsInstanceOf($columnDefinitions, ColumnDefinition::class);
        Assert::minCount($columnDefinitions, 2);
        Assert::isInstanceOf($columnDefinitions[0], SelectColumn::class, 'The first column should have "select" type');

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
        return array_map(
            fn (ColumnDefinition $columnDefinition): array => $columnDefinition->normalize(),
            $this->columnDefinitions
        );
    }

    /**
     * @return ColumnId[]
     */
    public function columnIds(): array
    {
        return \array_map(
            fn (ColumnDefinition $column): ColumnId => $column->id(),
            $this->columnDefinitions
        );
    }

    /**
     * @return ColumnCode[]
     */
    public function columnCodes(): array
    {
        return \array_map(
            fn (ColumnDefinition $column): ColumnCode => $column->code(),
            $this->columnDefinitions
        );
    }

    public function getFirstColumnId(): ColumnId
    {
        return $this->columnDefinitions[0]->id();
    }

    public function getFirstColumnCode(): ColumnCode
    {
        return $this->columnDefinitions[0]->code();
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
        foreach ($this->columnDefinitions as $columnDefinition) {
            if ($columnDefinition->id()->equals($columnId)) {
                return $columnDefinition;
            }
        }

        return null;
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
}
