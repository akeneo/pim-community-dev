<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Domain\Period;

use Akeneo\PerformanceAnalytics\Domain\Period;

final class Month implements Period
{
    private function __construct(private \DateTimeImmutable $date)
    {
    }

    public static function fromDate(\DateTimeImmutable $date): Month
    {
        return new Month($date);
    }

    public function toString(): string
    {
        return $this->date->format('Y-m');
    }
}
