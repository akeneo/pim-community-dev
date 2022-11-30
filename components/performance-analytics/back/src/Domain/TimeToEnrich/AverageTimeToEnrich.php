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

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\Period;

final class AverageTimeToEnrich
{
    private function __construct(
        private readonly string $aggregationCode,
        private readonly TimeToEnrichValue $timeToEnrichValue,
    ) {
    }

    public static function fromPeriodAndTimeToEnrichValue(Period $period, TimeToEnrichValue $timeToEnrichValue): AverageTimeToEnrich
    {
        return new AverageTimeToEnrich($period->toString(), $timeToEnrichValue);
    }

    public static function fromFamilyAndTimeToEnrichValue(FamilyCode $familyCode, TimeToEnrichValue $timeToEnrichValue): AverageTimeToEnrich
    {
        return new AverageTimeToEnrich($familyCode->toString(), $timeToEnrichValue);
    }

    public static function fromCategoryAndTimeToEnrichValue(CategoryCode $categoryCode, TimeToEnrichValue $timeToEnrichValue): AverageTimeToEnrich
    {
        return new AverageTimeToEnrich($categoryCode->toString(), $timeToEnrichValue);
    }

    /**
     * @return array{code: string, value: float}
     */
    public function normalize(): array
    {
        return [
            'code' => $this->aggregationCode,
            'value' => $this->timeToEnrichValue->value(),
        ];
    }

    public function code(): string
    {
        return $this->aggregationCode;
    }

    public function value(): TimeToEnrichValue
    {
        return $this->timeToEnrichValue;
    }
}
