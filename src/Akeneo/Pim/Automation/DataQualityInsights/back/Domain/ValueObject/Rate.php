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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class Rate
{
    /** @var int */
    private $rate;

    public function __construct(int $rate)
    {
        if ($rate < 0 || $rate > 100) {
            throw new \InvalidArgumentException('A rate must be an integer between 0 and 100');
        }

        $this->rate = $rate;
    }

    public function toInt(): int
    {
        return $this->rate;
    }

    public function isPerfect(): bool
    {
        return $this->rate === 100;
    }

    /**
     * @deprecated
     */
    public function __toString()
    {
        return $this->toLetter();
    }

    public function toLetter()
    {
        return Rank::fromRate($this)->toLetter();
    }
}
