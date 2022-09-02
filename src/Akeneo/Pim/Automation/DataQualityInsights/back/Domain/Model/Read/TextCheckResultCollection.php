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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextCheckResultCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<TextCheckResult>
     */
    private array $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function add(TextCheckResult $result): self
    {
        $this->results[] = $result;

        return $this;
    }

    public function normalize(): array
    {
        return array_map(function (TextCheckResult $result) {
            return $result->normalize();
        }, $this->results);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->results);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->results);
    }
}
