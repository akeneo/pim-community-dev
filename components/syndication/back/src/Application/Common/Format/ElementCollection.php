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

namespace Akeneo\Platform\Syndication\Application\Common\Format;

use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, ElementInterface>
 */
class ElementCollection implements \IteratorAggregate
{
    /** @var ElementInterface[] */
    private array $elements;

    private function __construct(array $elements)
    {
        Assert::allIsInstanceOf($elements, ElementInterface::class);

        $this->elements = $elements;
    }

    /**
     * @return ElementInterface[] | \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * @param ElementInterface[] $elements
     */
    public static function create(array $elements): self
    {
        return new self($elements);
    }
}
