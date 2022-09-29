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

namespace Akeneo\PerformanceAnalytics\Domain\Period;

use Akeneo\PerformanceAnalytics\Domain\Period;

final class Week implements Period
{
    private function __construct(private \DateTimeImmutable $date)
    {
    }

    public static function fromDate(\DateTimeImmutable $date): Week
    {
        return new Week($date);
    }

    public function toString(): string
    {
        // ISO-8601: Week can be represented as 2022-W01 or 2022W01
        return $this->date->format('o-\WW');
    }
}
