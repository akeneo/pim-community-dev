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

namespace Akeneo\Pim\TableAttribute\Domain\Value;

use Webmozart\Assert\Assert;

/**
 * @phpstan-implements \IteratorAggregate<int, Row>
 */
final class Table implements \IteratorAggregate
{
    /** @var array<Row> */
    private array $rows;

    /**
     * @param Row[] $rows
     */
    private function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return \Traversable<Row>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->rows);
    }

    /**
     * @param array<int,array> $data
     */
    public static function fromNormalized(array $data): self
    {
        Assert::notEmpty($data);
        Assert::allIsArray($data);
        return new self(\array_values(
            \array_map(
                fn (array $row): Row => Row::fromNormalized($row),
                $data
            )
        ));
    }

    /**
     * @return array<array>
     */
    public function normalize(): array
    {
        return array_map(
            fn (Row $row): array => $row->normalize(),
            $this->rows
        );
    }

    /**
     * @return string[]
     */
    public function uniqueColumnCodes(): array
    {
        return \array_unique(\array_merge(...\array_map(fn (Row $row): array => $row->columnCodes(), $this->rows)));
    }
}
