<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, Column>
 */
class ColumnCollection implements \IteratorAggregate
{
    private function __construct(
        private array $columns,
    ) {
        Assert::allIsInstanceOf($columns, Column::class);
    }

    public static function create(array $columns): self
    {
        return new self($columns);
    }

    public static function createFromNormalized(array $normalizedColumns): self
    {
        return new self(array_map(static fn (array $normalizedColumn) => Column::createFromNormalized($normalizedColumn), $normalizedColumns));
    }

    public function getColumnUuids(): array
    {
        return array_map(static fn (Column $column) => $column->getUuid(), $this->columns);
    }

    /**
     * @return Column[]|\ArrayIterator<int, Column>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->columns);
    }

    public function getLabels(): array
    {
        return array_map(static fn (Column $column) => $column->getLabel(), $this->columns);
    }

    public function normalize(): array
    {
        return array_map(static fn (Column $column) => $column->normalize(), $this->columns);
    }
}
