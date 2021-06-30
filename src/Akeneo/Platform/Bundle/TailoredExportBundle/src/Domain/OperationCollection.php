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

namespace Akeneo\Platform\TailoredExport\Domain;

use Webmozart\Assert\Assert;

class OperationCollection implements \IteratorAggregate
{
    /** @var Operation[] */
    private array $operations = [];

    private function __construct(array $operations)
    {
        Assert::allIsInstanceOf($operations, Operation::class);
        $this->operations = $operations;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->operations);
    }

    /**
     * @param Operation[] $operations
     * @return OperationCollection
     */
    public static function create(array $operations): self
    {
        return new self($operations);
    }
}