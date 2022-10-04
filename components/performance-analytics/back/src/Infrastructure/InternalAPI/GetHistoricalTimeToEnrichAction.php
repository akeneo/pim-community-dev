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

use Akeneo\PerformanceAnalytics\Application\Exception\InvalidQueryException;
use Akeneo\PerformanceAnalytics\Application\Query\GetHistoricalTimeToEnrich;
use Akeneo\PerformanceAnalytics\Application\Query\GetHistoricalTimeToEnrichHandler;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetHistoricalTimeToEnrichAction
{
    public function __construct(
        private GetHistoricalTimeToEnrichHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $averageTimeToEnrichCollection = ($this->handler)($this->buildQueryFromRequest($request));
        } catch (InvalidQueryException $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse($averageTimeToEnrichCollection->normalize());
    }

    private function buildQueryFromRequest(Request $request): GetHistoricalTimeToEnrich
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

        return new GetHistoricalTimeToEnrich(
            $startDate,
            $endDate,
            $periodType
        );
    }
}
