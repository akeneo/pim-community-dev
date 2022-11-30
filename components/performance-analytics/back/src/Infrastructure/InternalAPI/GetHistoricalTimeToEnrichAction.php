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
use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\PeriodType;
use Akeneo\PerformanceAnalytics\Domain\TimeToEnrich\AverageTimeToEnrichQuery;
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
            $periodType = PeriodType::from($request->get('period_type'));
        } catch (\Throwable) {
            throw new BadRequestHttpException('The "period_type" parameter is not a valid period type.');
        }

        $filters = [];
        foreach (['channels', 'locales', 'families', 'categories'] as $filterName) {
            if (null !== $request->get($filterName) && '' !== $request->get($filterName)) {
                if (!is_string($request->get($filterName))) {
                    throw new BadRequestHttpException(sprintf('The "%s" parameter should be a string.', $filterName));
                }

                $filters[$filterName] = explode(',', $request->get($filterName));
                try {
                    $filters[$filterName] = array_map(fn (string $code) => match ($filterName) {
                        'channels' => ChannelCode::fromString($code),
                        'locales' => LocaleCode::fromString($code),
                        'families' => FamilyCode::fromString($code),
                        'categories' => CategoryCode::fromString($code),
                    }, $filters[$filterName]);
                } catch (\Exception) {
                    throw new BadRequestHttpException(sprintf('The "%s" parameter is not valid.', $filterName));
                }
            }
        }

        return new GetHistoricalTimeToEnrich(new AverageTimeToEnrichQuery(
            $startDate,
            $endDate,
            $periodType,
            /* @phpstan-ignore-next-line */
            $filters['channels'] ?? null,
            /* @phpstan-ignore-next-line */
            $filters['locales'] ?? null,
            /* @phpstan-ignore-next-line */
            $filters['families'] ?? null,
            /* @phpstan-ignore-next-line */
            $filters['categories'] ?? null
        ));
    }
}
