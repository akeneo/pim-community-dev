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

namespace Akeneo\Platform\TailoredExport\Application\Query\Source;

use Webmozart\Assert\Assert;

class SourceCollection implements \IteratorAggregate
{
    /** @var SourceInterface[] */
    private array $sources = [];

    private function __construct(array $sources)
    {
        Assert::allIsInstanceOf($sources, SourceInterface::class);

        $this->sources = $sources;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->sources);
    }

    /**
     * @param SourceInterface[] $sources
     * @return SourceCollection
     */
    public static function create(array $sources): self
    {
        return new self($sources);
    }
}
