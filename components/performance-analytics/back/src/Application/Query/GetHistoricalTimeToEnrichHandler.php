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

namespace Akeneo\PerformanceAnalytics\Application\Query;

use Akeneo\PerformanceAnalytics\Application\Exception\InvalidQueryException;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichCollection;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichRepository;

final class GetHistoricalTimeToEnrichHandler
{
    public function __construct(private AverageTimeToEnrichRepository $averageTimeToEnrichRepository)
    {
    }

    public function __invoke(GetHistoricalTimeToEnrich $getHistoricalTimeToEnrich): AverageTimeToEnrichCollection
    {
        if ($getHistoricalTimeToEnrich->startDate() > $getHistoricalTimeToEnrich->endDate()) {
            throw new InvalidQueryException('Start date can not be superior to end date.');
        }

        return $this->averageTimeToEnrichRepository->search(
            $getHistoricalTimeToEnrich->startDate(),
            $getHistoricalTimeToEnrich->endDate(),
            $getHistoricalTimeToEnrich->aggregationPeriodType(),
            $getHistoricalTimeToEnrich->channelCodesFilter(),
            $getHistoricalTimeToEnrich->localeCodesFilter(),
            $getHistoricalTimeToEnrich->familyCodesFilter(),
            $getHistoricalTimeToEnrich->categoryCodesFilter()
        );
    }
}
