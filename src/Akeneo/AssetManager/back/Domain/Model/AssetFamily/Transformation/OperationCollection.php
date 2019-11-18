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

final class OperationCollection implements \IteratorAggregate
{
    /** @var Operation[] */
    private $operations = [];

    private function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->operations);
    }

    public static function create(array $operations): OperationCollection
    {
        return new self($operations);
    }
}
