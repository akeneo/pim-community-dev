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

namespace Akeneo\PerformanceAnalytics\Infrastructure\InternalAPI;

use Akeneo\PerformanceAnalytics\Application\Query\GetHistoricalTimeToEnrich;
use Akeneo\PerformanceAnalytics\Application\Query\GetHistoricalTimeToEnrichHandler;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetHistoricalTimeToEnrichAction
{
    public function __construct(
        private GetHistoricalTimeToEnrichHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!is_string($request->get('start_date'))) {
            throw new BadRequestHttpException('The "start_date" parameter is required and should be a string.');
        }
        try {
            $startDate = new \DateTimeImmutable($request->get('start_date'));
        } catch (\Exception) {
            throw new BadRequestHttpException('The "start_date" parameter is not a valid date.');
        }

        if (!is_string($request->get('end_date'))) {
            throw new BadRequestHttpException('The "end_date" parameter is required and should be a string.');
        }
        try {
            $endDate = new \DateTimeImmutable($request->get('end_date'));
        } catch (\Exception) {
            throw new BadRequestHttpException('The "end_date" parameter is not a valid date.');
        }

        if (!is_string($request->get('period_type'))) {
            throw new BadRequestHttpException('The "period_type" parameter is required and should be a string.');
        }
        try {
            $periodType = PeriodType::fromString($request->get('period_type'));
        } catch (\Exception) {
            throw new BadRequestHttpException('The "period_type" parameter is not a valid period type.');
        }

        $query = new GetHistoricalTimeToEnrich(
            $startDate,
            $endDate,
            $periodType
        );

        $averageTimeToEnrichCollection = ($this->handler)($query);

        return new JsonResponse($averageTimeToEnrichCollection->normalize());
    }
}
