<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;

final class AxisRankCollection implements \IteratorAggregate
{
    private $axesRanks = [];

    public function add(AxisCode $axisCode, ChannelLocaleRankCollection $ranks): self
    {
        $this->axesRanks[strval($axisCode)] = $ranks;

        return $this;
    }

    public function get(AxisCode $axisCode): ?ChannelLocaleRankCollection
    {
        return $this->axesRanks[strval($axisCode)] ?? null;
    }

    public function toArrayInt(): array
    {
        return array_map(function (ChannelLocaleRankCollection $ranks) {
            return $ranks->toArrayInt();
        }, $this->axesRanks);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->axesRanks);
    }
}
