<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Query\Column;

use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;
use Webmozart\Assert\Assert;

class ColumnCollection implements \IteratorAggregate
{
    /** @var Column[] */
    private array $columns = [];

    private function __construct(array $columns)
    {
        Assert::allIsInstanceOf($columns, Column::class);

        $this->columns = $columns;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->columns);
    }

    /**
     * @param Column[] $columns
     * @return ColumnCollection
     */
    public static function create(array $columns): self
    {
        return new self($columns);
    }

    public function getAllSources(): SourceCollection
    {
        $sources = array_reduce(
            $this->columns,
            fn (array $result, Column $column) =>
            [...$result, ...$column->getSourceCollection()],
            []
        );

        return SourceCollection::create($sources);
    }
}
