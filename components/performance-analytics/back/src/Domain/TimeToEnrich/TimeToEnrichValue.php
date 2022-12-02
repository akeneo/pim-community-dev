<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

use Webmozart\Assert\Assert;

final class TimeToEnrichValue
{
    private function __construct(
        private readonly float $hours,
    ) {
        Assert::greaterThanEq($hours, 0, 'Time to enrich value in hours must be greater or equal to zero');
    }

    public static function fromHours(float $hours): TimeToEnrichValue
    {
        return new TimeToEnrichValue($hours);
    }

    public function inDays(): float
    {
        return round($this->hours / 24, 2);
    }

    public function inHours(): float
    {
        return $this->hours;
    }
}
