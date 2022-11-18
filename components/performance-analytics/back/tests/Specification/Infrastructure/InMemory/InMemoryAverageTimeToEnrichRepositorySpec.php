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

namespace Specification\Akeneo\PerformanceAnalytics\Infrastructure\InMemory;

use Akeneo\PerformanceAnalytics\Domain\AggregationType;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use PhpSpec\ObjectBehavior;

class InMemoryAverageTimeToEnrichRepositorySpec extends ObjectBehavior
{
    public function it_searches_average_time_to_enrich_by_week(): void
    {
        $startDate = new \DateTimeImmutable('2022-09-01');
        $averageTimeToEnrichList = $this->search(
            $startDate,
            $startDate->modify('+4 weeks'),
            PeriodType::WEEK,
            AggregationType::FAMILIES
        );
        $averageTimeToEnrichList->shouldHaveType(AverageTimeToEnrichCollection::class);
        $averageTimeToEnrichList->normalize()->shouldHaveCount(5);
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('period');
        $averageTimeToEnrichList->normalize()[0]['period']->shouldBe('2022-W35');
        $averageTimeToEnrichList->normalize()[3]['period']->shouldBe('2022-W38');
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('value');
    }

    public function it_searches_average_time_to_enrich_by_day(): void
    {
        $startDate = new \DateTimeImmutable('2022-09-01');
        $averageTimeToEnrichList = $this->search(
            $startDate,
            $startDate->modify('+6 DAY'),
            PeriodType::DAY,
            AggregationType::FAMILIES
        );

        $averageTimeToEnrichList->shouldHaveType(AverageTimeToEnrichCollection::class);
        $averageTimeToEnrichList->normalize()->shouldHaveCount(7);
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('period');
        $averageTimeToEnrichList->normalize()[0]['period']->shouldBe('2022-09-01');
        $averageTimeToEnrichList->normalize()[6]['period']->shouldBe('2022-09-07');
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('value');
    }

    public function it_searches_average_time_to_enrich_by_month(): void
    {
        $startDate = new \DateTimeImmutable('2022-09-01');
        $averageTimeToEnrichList = $this->search(
            $startDate,
            $startDate->modify('+3 MONTH'),
            PeriodType::MONTH,
            AggregationType::FAMILIES
        );

        $averageTimeToEnrichList->shouldHaveType(AverageTimeToEnrichCollection::class);
        $averageTimeToEnrichList->normalize()->shouldHaveCount(4);
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('period');
        $averageTimeToEnrichList->normalize()[0]['period']->shouldBe('2022-09');
        $averageTimeToEnrichList->normalize()[3]['period']->shouldBe('2022-12');
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('value');
    }

    public function it_searches_average_time_to_enrich_by_year(): void
    {
        $startDate = new \DateTimeImmutable('2022-09-01');
        $averageTimeToEnrichList = $this->search(
            $startDate,
            $startDate->modify('+3 YEAR'),
            PeriodType::YEAR,
            AggregationType::FAMILIES
        );

        $averageTimeToEnrichList->shouldHaveType(AverageTimeToEnrichCollection::class);
        $averageTimeToEnrichList->normalize()->shouldHaveCount(4);
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('period');
        $averageTimeToEnrichList->normalize()[0]['period']->shouldBe('2022');
        $averageTimeToEnrichList->normalize()[3]['period']->shouldBe('2025');
        $averageTimeToEnrichList->normalize()[0]->shouldHaveKey('value');
    }
}
