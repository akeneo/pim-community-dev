<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

final class PurgeOutdatedData
{
    public const RETENTION_DAYS = 7;
    public const RETENTION_WEEKS = 4;
    public const RETENTION_MONTHS = 15;
    public const RETENTION_YEARS = 3;

    /** @var DashboardRatesProjectionRepositoryInterface */
    private $dashboardRatesProjectionRepository;

    /** @var CriterionEvaluationRepositoryInterface */
    private $criterionEvaluationRepository;

    /** @var ProductAxisRateRepositoryInterface */
    private $productAxisRateRepository;

    public function __construct(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        ProductAxisRateRepositoryInterface $productAxisRateRepository
    ) {
        $this->dashboardRatesProjectionRepository = $dashboardRatesProjectionRepository;
        $this->criterionEvaluationRepository = $criterionEvaluationRepository;
        $this->productAxisRateRepository = $productAxisRateRepository;
    }

    public function purgeDashboardProjectionRatesFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = new ConsolidationDate($date);

        $this->dashboardRatesProjectionRepository->removeRates(
            TimePeriod::daily(),
            $purgeDate->modify(sprintf('-%d DAY', self::RETENTION_DAYS))
        );

        if ($purgeDate->isLastDayOfWeek()) {
            $this->dashboardRatesProjectionRepository->removeRates(
                TimePeriod::weekly(),
                $purgeDate->modify(sprintf('-%d WEEK', self::RETENTION_WEEKS))
            );
        }

        if ($purgeDate->isLastDayOfMonth()) {
            $this->dashboardRatesProjectionRepository->removeRates(
                TimePeriod::monthly(),
                $purgeDate->modify(sprintf('-%d MONTH', self::RETENTION_MONTHS))
            );
        }

        if ($purgeDate->isLastDayOfYear()) {
            $this->dashboardRatesProjectionRepository->removeRates(
                TimePeriod::yearly(),
                $purgeDate->modify(sprintf('-%d YEAR', self::RETENTION_YEARS))
            );
        }
    }

    public function purgeCriterionEvaluationsFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = $date->modify(sprintf('-%d DAY', self::RETENTION_DAYS));
        $this->criterionEvaluationRepository->purgeUntil($purgeDate);
    }

    public function purgeProductAxisRatesFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = $date->modify(sprintf('-%d DAY', self::RETENTION_DAYS));
        $this->productAxisRateRepository->purgeUntil($purgeDate);
    }
}
