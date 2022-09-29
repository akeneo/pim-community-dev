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

use Akeneo\PerformanceAnalytics\Domain\Period;

final class AverageTimeToEnrich
{
    private function __construct(
        private Period $period,
        private TimeToEnrichValue $timeToEnrichValue,
    ) {
    }

    public static function fromPeriodAndTimeToEnrichValue(Period $period, TimeToEnrichValue $timeToEnrichValue): AverageTimeToEnrich
    {
        return new AverageTimeToEnrich($period, $timeToEnrichValue);
    }

    /**
     * @return array{period: string, value: float}
     */
    public function normalize(): array
    {
        return [
            'period' => $this->period->toString(),
            'value' => $this->timeToEnrichValue->value(),
        ];
    }
}
