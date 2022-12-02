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
    private const DATETIME_FORMAT = 'Y-m';

    private function __construct(private \DateTimeImmutable $date)
    {
    }

    public static function fromDate(\DateTimeImmutable $date): Month
    {
        return new Month($date);
    }

    public static function fromString(string $date): Month
    {
        $dateTime = \DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $date);

        if (!$dateTime instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException(sprintf('Date "%s" does not represent a month at format "%s"', $date, self::DATETIME_FORMAT));
        }

        return new Month($dateTime);
    }

    public function toString(): string
    {
        return $this->date->format(self::DATETIME_FORMAT);
    }
}
