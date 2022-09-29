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

namespace Akeneo\PerformanceAnalytics\Domain;

use Webmozart\Assert\Assert;

final class PeriodType
{
    private const DAY = 'day';
    private const WEEK = 'week';
    private const MONTH = 'month';
    private const YEAR = 'year';

    private function __construct(private string $type)
    {
    }

    public static function day(): PeriodType
    {
        return new self(self::DAY);
    }

    public static function week(): PeriodType
    {
        return new self(self::WEEK);
    }

    public static function month(): PeriodType
    {
        return new self(self::MONTH);
    }

    public static function year(): PeriodType
    {
        return new self(self::YEAR);
    }

    public static function fromString(string $periodType): PeriodType
    {
        Assert::oneOf($periodType, [self::DAY, self::WEEK, self::MONTH, self::YEAR]);

        return new self($periodType);
    }

    public function toString(): string
    {
        return $this->type;
    }
}
