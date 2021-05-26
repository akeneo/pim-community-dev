<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Webmozart\Assert\Assert;

class OperationCollection implements \IteratorAggregate
{
    /** @var Operation[] */
    private array $operations = [];

    private function __construct(array $operations)
    {
        Assert::notEmpty($operations);
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

    public function normalize(): array
    {
        return array_map(fn (Operation $operation) => $operation->normalize(), $this->operations);
    }

    public function equals(OperationCollection $otherOperationCollection): bool
    {
        return $this->normalize() === $otherOperationCollection->normalize();
    }

    public function hasOperation(string $operationType): bool
    {
        return in_array($operationType, array_column($this->normalize(), 'type'), true);
    }
}
