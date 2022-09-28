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
    private function __construct(private float $value)
    {
        Assert::greaterThanEq($this->value, 0);
    }

    public static function fromValue(float $value): TimeToEnrichValue
    {
        return new TimeToEnrichValue($value);
    }

    public function value(): float
    {
        return $this->value;
    }
}
