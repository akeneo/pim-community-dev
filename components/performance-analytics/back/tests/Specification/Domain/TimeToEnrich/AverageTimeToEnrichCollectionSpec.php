<?php

declare(strict_types=1);

namespace Specification\Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\PerformanceAnalytics\Domain\Period\Week;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrich;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\TimeToEnrichValue;
use PhpSpec\ObjectBehavior;

final class AverageTimeToEnrichCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $timeToEnrich1 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-01-01')),
            TimeToEnrichValue::fromValue(1)
        );
        $timeToEnrich2 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-02-01')),
            TimeToEnrichValue::fromValue(3)
        );

        $this->beConstructedThrough('fromList', [[$timeToEnrich1, $timeToEnrich2]]);
    }

    public function it_is_a_collection_of_time_to_enrich()
    {
        $this->shouldHaveType(AverageTimeToEnrichCollection::class);
    }

    public function it_raises_an_exception_when_list_does_not_contain_time_to_enrich()
    {
        $timeToEnrich1 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-01-01')),
            TimeToEnrichValue::fromValue(1)
        );
        $timeToEnrich2 = new \stdClass();

        $this->beConstructedThrough('fromList', [[$timeToEnrich1, $timeToEnrich2]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_normalizes_from_array()
    {
        $this->normalize()->shouldReturn([
            [
                'code' => '2020-W53',
                'value' => (float) 1,
            ],
            [
                'code' => '2021-W05',
                'value' => (float) 3,
            ],
        ]);
    }

    public function it_normalizes_from_traversable()
    {
        $timeToEnrich1 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-01-01')),
            TimeToEnrichValue::fromValue(1)
        );
        $timeToEnrich2 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-02-01')),
            TimeToEnrichValue::fromValue(3)
        );

        $this->beConstructedThrough('fromList', [new \ArrayIterator([$timeToEnrich1, $timeToEnrich2])]);
        $this->normalize()->shouldReturn([
            [
                'code' => '2020-W53',
                'value' => (float) 1,
            ],
            [
                'code' => '2021-W05',
                'value' => (float) 3,
            ],
        ]);
    }

    public function it_normalizes_from_generator()
    {
        $timeToEnrich1 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-01-01')),
            TimeToEnrichValue::fromValue(1)
        );
        $timeToEnrich2 = AverageTimeToEnrich::fromPeriodAndTimeToEnrichValue(
            Week::fromDate(new \DateTimeImmutable('2021-02-01')),
            TimeToEnrichValue::fromValue(3)
        );

        $this->beConstructedThrough('fromList', [yield from [$timeToEnrich1, $timeToEnrich2]]);
        $this->normalize()->shouldReturn([
            [
                'code' => '2020-W53',
                'value' => (float) 1,
            ],
            [
                'code' => '2021-W05',
                'value' => (float) 3,
            ],
        ]);
    }
}
