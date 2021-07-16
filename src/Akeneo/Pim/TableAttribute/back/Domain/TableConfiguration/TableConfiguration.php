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
        Assert::allIsInstanceOf($columnDefinitions, ColumnDefinition::class);
        Assert::minCount($columnDefinitions, 2);

        $codes = \array_map(
            fn (ColumnDefinition $definition): string => $definition->code()->asString(),
            $columnDefinitions
        );
        Assert::uniqueValues($codes);

        return new self(array_values($columnDefinitions));
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
     * @return ColumnCode[]
     */
    public function columnCodes(): array
    {
        return \array_map(
            fn (ColumnDefinition $column): ColumnCode => $column->code(),
            $this->columnDefinitions
        );
    }

    public function getColumnDataType(ColumnCode $columnCode): ?ColumnDataType
    {
        foreach ($this->columnDefinitions as $columnDefinition) {
            if ($columnDefinition->code()->equals($columnCode)) {
                return $columnDefinition->dataType();
            }
        }

        return null;
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
        return \array_values(\array_filter($this->columnDefinitions,
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
}
