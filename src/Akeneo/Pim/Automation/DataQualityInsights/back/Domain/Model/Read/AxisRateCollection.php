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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;

final class AxisRateCollection implements \IteratorAggregate
{
    private $axesRates = [];

    public function add(AxisCode $axisCode, ChannelLocaleRateCollection $rates): self
    {
        $this->axesRates[strval($axisCode)] = $rates;

        return $this;
    }

    public function get(AxisCode $axisCode): ?ChannelLocaleRateCollection
    {
        return $this->axesRates[strval($axisCode)] ?? null;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->axesRates);
    }
}
