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

namespace Akeneo\Platform\TailoredExport\Domain\Model\Operation;

use Webmozart\Assert\Assert;

class OperationCollection implements \IteratorAggregate
{
    /** @var OperationInterface[] */
    private array $operations = [];

    private function __construct(array $operations)
    {
        Assert::allIsInstanceOf($operations, OperationInterface::class);

        $this->operations = $operations;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->operations);
    }

    /**
     * @param OperationInterface[] $operations
     * @return OperationCollection
     */
    public static function create(array $operations): self
    {
        return new self($operations);
    }

    public static function createFromNormalized(array $normalizedOperations): self
    {
        $operations = [];
        foreach ($normalizedOperations as $normalizedOperation) {
            $operation = self::hydrateOperation($normalizedOperation);

            if (null !== $operation) {
                $operations[] = $operation;
            }
        }

        return self::create($operations);
    }

    private static function hydrateOperation(array $normalizedOperation): ?OperationInterface
    {
        switch ($normalizedOperation['type']) {
            case 'replacement':
                return ReplacementOperation::createFromNormalized($normalizedOperation);
            case 'default_value':
                return DefaultValueOperation::createFromNormalized($normalizedOperation);
            default:
                return null;
        }
    }
}
