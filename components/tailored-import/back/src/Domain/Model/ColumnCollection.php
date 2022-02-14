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

class ColumnCollection
{
    private function __construct(
        private array $columns,
    ) {
        Assert::allIsInstanceOf($columns, Column::class);
    }

    public static function createFromNormalized(array $normalizedColumns): self
    {
        $columnInstances = array_map(static fn (array $column) => Column::createFromNormalized($column), $normalizedColumns);

        return new self($columnInstances);
    }

    public function getColumnUuids(): array
    {
        return array_map(fn (Column $column) => $column->getUuid(), $this->columns);
    }

    /**
     * @return \ArrayIterator<int, Column>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->columns);
    }

    public function getLabels(): array
    {
        return array_map(fn (Column $column) => $column->label(), $this->columns);
    }
}
