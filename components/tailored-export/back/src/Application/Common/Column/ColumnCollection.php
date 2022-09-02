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

namespace Akeneo\Platform\TailoredExport\Application\Common\Column;

use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, Column>
 */
class ColumnCollection implements \IteratorAggregate
{
    /** @var Column[] */
    private array $columns = [];

    private function __construct(array $columns)
    {
        Assert::notEmpty($columns, 'Export structure has not been configured for this job.');
        Assert::allIsInstanceOf($columns, Column::class);

        $this->columns = $columns;
    }

    /**
     * @return Column[]|\Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->columns);
    }

    /**
     * @param Column[] $columns
     */
    public static function create(array $columns): self
    {
        return new self($columns);
    }

    public function getAllSources(): SourceCollection
    {
        $sources = array_reduce(
            $this->columns,
            static fn (array $result, Column $column) => [...$result, ...$column->getSourceCollection()],
            [],
        );

        return SourceCollection::create($sources);
    }
}
