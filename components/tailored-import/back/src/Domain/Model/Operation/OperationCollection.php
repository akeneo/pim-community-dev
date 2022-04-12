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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, OperationInterface>
 */
class OperationCollection implements \IteratorAggregate
{
    /** @var OperationInterface[] */
    private array $operations = [];

    private function __construct(array $operations)
    {
        Assert::allIsInstanceOf($operations, OperationInterface::class);

        $this->operations = $operations;
    }

    /**
     * @return OperationInterface[]|\Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->operations);
    }

    /**
     * @param OperationInterface[] $operations
     */
    public static function create(array $operations): self
    {
        return new self($operations);
    }
}
