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

namespace Specification\Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\Period\Week;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrich;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\TimeToEnrichValue;
use PhpSpec\ObjectBehavior;

final class AverageTimeToEnrichSpec extends ObjectBehavior
{
    public function it_normalizes_average_tte_from_family(): void
    {
        $timeToEnrichValue = TimeToEnrichValue::fromHours(50.4);
        $this->beConstructedThrough('fromFamilyAndTimeToEnrichValue', [FamilyCode::fromString('shoes'), $timeToEnrichValue]);
        $this->normalize()->shouldReturn([
            'code' => 'shoes',
            'value' => 2.10,
        ]);
    }

    public function it_normalizes_average_tte_from_category(): void
    {
        $timeToEnrichValue = TimeToEnrichValue::fromHours(50.4);
        $this->beConstructedThrough('fromCategoryAndTimeToEnrichValue', [CategoryCode::fromString('webcam'), $timeToEnrichValue]);
        $this->normalize()->shouldReturn([
            'code' => 'webcam',
            'value' => 2.10,
        ]);
    }

    public function it_normalizes_average_tte_from_period(): void
    {
        $period = Week::fromDate(new \DateTimeImmutable('2022-09-10'));
        $timeToEnrichValue = TimeToEnrichValue::fromHours(50.4);
        $this->beConstructedThrough('fromPeriodAndTimeToEnrichValue', [$period, $timeToEnrichValue]);
        $this->shouldHaveType(AverageTimeToEnrich::class);

        $this->normalize()->shouldReturn([
            'code' => '2022-W36',
            'value' => 2.10,
        ]);
    }
}
