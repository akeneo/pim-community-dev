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

use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use PhpSpec\ObjectBehavior;

class InMemoryAverageTimeToEnrichRepositorySpec extends ObjectBehavior
{
    public function it_searches_average_time_to_enrich()
    {
        $startDate = new \DateTimeImmutable('2022-09-01');
        $averageTimeToEnrichList = $this->search(
            $startDate,
            $startDate->modify('+4 weeks'),
            PeriodType::week()
        );
        $averageTimeToEnrichList->shouldHaveType(AverageTimeToEnrichCollection::class);
        $averageTimeToEnrichList->normalize()->shouldHaveCount(5);
    }
}
