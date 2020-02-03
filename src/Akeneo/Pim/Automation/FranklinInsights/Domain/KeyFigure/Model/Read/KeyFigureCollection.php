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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read;

final class KeyFigureCollection implements \IteratorAggregate
{
    /** @var KeyFigure[] */
    private $collection;

    /**
     * @param KeyFigure[] $collection
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->collection);
    }

    public function merge(KeyFigureCollection $keyFigureCollection): KeyFigureCollection
    {
        return new self(array_merge($this->collection, iterator_to_array($keyFigureCollection)));
    }
}
