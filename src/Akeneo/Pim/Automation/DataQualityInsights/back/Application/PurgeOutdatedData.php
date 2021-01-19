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
    public const EVALUATIONS_RETENTION_DAYS = 1;

    public const CONSOLIDATION_RETENTION_DAYS = 7;
    public const CONSOLIDATION_RETENTION_WEEKS = 4;
    public const CONSOLIDATION_RETENTION_MONTHS = 15;
    public const CONSOLIDATION_RETENTION_YEARS = 3;

    public const PURGE_BATCH_SIZE = 1000;
    public const DEFAULT_MAX_PURGE = 1000000;

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
            $purgeDate->modify(sprintf('-%d DAY', self::CONSOLIDATION_RETENTION_DAYS))
        );

        if ($purgeDate->isLastDayOfWeek()) {
            $this->dashboardRatesProjectionRepository->removeRates(
                TimePeriod::weekly(),
                $purgeDate->modify(sprintf('-%d WEEK', self::CONSOLIDATION_RETENTION_WEEKS))
            );
        }

        if ($purgeDate->isLastDayOfMonth()) {
            $this->dashboardRatesProjectionRepository->removeRates(
                TimePeriod::monthly(),
                $purgeDate->modify(sprintf('-%d MONTH', self::CONSOLIDATION_RETENTION_MONTHS))
            );
        }

        if ($purgeDate->isLastDayOfYear()) {
            $this->dashboardRatesProjectionRepository->removeRates(
                TimePeriod::yearly(),
                $purgeDate->modify(sprintf('-%d YEAR', self::CONSOLIDATION_RETENTION_YEARS))
            );
        }
    }

    public function purgeOutdatedCriterionEvaluations(?int $max = null): void
    {
        $this->criterionEvaluationRepository->purgeEvaluationsWithoutProducts(self::PURGE_BATCH_SIZE, $max ?? self::DEFAULT_MAX_PURGE);
        $this->criterionEvaluationRepository->purgeOutdatedEvaluations(self::PURGE_BATCH_SIZE, $max ?? self::DEFAULT_MAX_PURGE);
    }

    public function purgeProductAxisRatesFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = $date->modify(sprintf('-%d DAY', self::CONSOLIDATION_RETENTION_DAYS));
        $this->productAxisRateRepository->purgeUntil($purgeDate);
    }
}
