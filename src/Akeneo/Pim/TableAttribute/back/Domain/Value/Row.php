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
 * @phpstan-implements \IteratorAggregate<string, Cell>
 */
final class Row implements \IteratorAggregate
{
    /** @var array<string, Cell> */
    private array $cells;

    /**
     * @param array<string, Cell> $cells
     */
    private function __construct(array $cells)
    {
        $this->cells = $cells;
    }

    /**
     * @return \Traversable<string, Cell>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->cells);
    }

    /**
     * @param array<string, mixed> $row
     * @return static
     */
    public static function fromNormalized(array $row): self
    {
        Assert::notEmpty($row);
        return new self(
            array_map(
                fn ($data): Cell => Cell::fromNormalized($data),
                $row
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return array_map(
            fn (Cell $cell) => $cell->normalize(),
            $this->cells
        );
    }

    /**
     * @return string[]
     */
    public function columnCodes(): array
    {
        return \array_map(
            'strval',
            \array_keys($this->cells)
        );
    }
}
