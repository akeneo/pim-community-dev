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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Rates;

final class Rate
{
    /** @var int */
    private $rate;

    public function __construct(int $rate)
    {
        if ($rate < 0 || $rate >100) {
            throw new \InvalidArgumentException('A rate must be an integer between 0 and 100');
        }

        $this->rate = $rate;
    }

    public function toInt(): int
    {
        return $this->rate;
    }

    public function __toString()
    {
        switch (true) {
            case ($this->rate >= 90):
                return Rates::RANK_1;
            case ($this->rate >= 80):
                return Rates::RANK_2;
            case ($this->rate >= 70):
                return Rates::RANK_3;
            case ($this->rate >= 60):
                return Rates::RANK_4;
            default:
                return Rates::RANK_5;
        }
    }
}
