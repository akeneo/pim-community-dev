<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardPurgeDateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

final class PurgeOutdatedData
{
    public const EVALUATIONS_RETENTION_DAYS = 1;

    public const CONSOLIDATION_RETENTION_DAYS = 7;
    public const CONSOLIDATION_RETENTION_WEEKS = 4;
    public const CONSOLIDATION_RETENTION_MONTHS = 15;
    public const CONSOLIDATION_RETENTION_YEARS = 3;

    private DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository;

    public function __construct(DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository)
    {
        $this->dashboardScoresProjectionRepository = $dashboardScoresProjectionRepository;
    }

    public function purgeDashboardProjectionRatesFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = new ConsolidationDate($date);
        $purgeDates = new DashboardPurgeDateCollection();
        $daily = TimePeriod::daily();

        for ($day = PurgeOutdatedData::CONSOLIDATION_RETENTION_DAYS; $day < PurgeOutdatedData::CONSOLIDATION_RETENTION_DAYS * 2; $day++) {
            $purgeDates->add($daily, $purgeDate->modify(sprintf('-%d DAY', $day)));
        }

        $purgeDates
            ->add(
                TimePeriod::weekly(),
                $purgeDate->modify(sprintf('-%d WEEK', self::CONSOLIDATION_RETENTION_WEEKS))->modify('Sunday last week')
            )
            ->add(
                TimePeriod::monthly(),
                $purgeDate->modify(sprintf('-%d MONTH', self::CONSOLIDATION_RETENTION_MONTHS))->modify('Last day of last month')
            )
            ->add(
                TimePeriod::yearly(),
                $purgeDate->modify(sprintf('-%d YEAR', self::CONSOLIDATION_RETENTION_YEARS))->modify('Last day of december last year')
            );

        $this->dashboardScoresProjectionRepository->purgeRates($purgeDates);
    }
}
