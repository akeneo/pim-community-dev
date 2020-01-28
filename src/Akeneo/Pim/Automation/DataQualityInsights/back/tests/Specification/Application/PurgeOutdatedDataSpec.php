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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\PurgeOutdatedData;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PurgeOutdatedDataSpec extends ObjectBehavior
{
    public function let(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        ProductAxisRateRepositoryInterface $productAxisRateRepository
    ) {
        $this->beConstructedWith($dashboardRatesProjectionRepository, $criterionEvaluationRepository, $productAxisRateRepository);
    }

    public function it_purges_daily_dashboard_projection_rates(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $purgeDate = new \DateTimeImmutable('2020-01-08');

        $dashboardRatesProjectionRepository->removeRates(Periodicity::daily(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d DAY', PurgeOutdatedData::RETENTION_DAYS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $this->purgeDashboardProjectionRatesFrom($purgeDate);
    }

    public function it_purges_daily_and_weekly_dashboard_rates_projection_at_the_last_day_of_the_week(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $purgeDate = new \DateTimeImmutable('2019-12-29');

        $dashboardRatesProjectionRepository->removeRates(Periodicity::daily(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d DAY', PurgeOutdatedData::RETENTION_DAYS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $dashboardRatesProjectionRepository->removeRates(Periodicity::weekly(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d WEEK', PurgeOutdatedData::RETENTION_WEEKS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $this->purgeDashboardProjectionRatesFrom($purgeDate);
    }

    public function it_purges_daily_and_monthly_dashboard_rates_projection_at_the_last_day_of_the_month(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $purgeDate = new \DateTimeImmutable('2019-10-31');

        $dashboardRatesProjectionRepository->removeRates(Periodicity::daily(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d DAY', PurgeOutdatedData::RETENTION_DAYS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $dashboardRatesProjectionRepository->removeRates(Periodicity::monthly(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d MONTH', PurgeOutdatedData::RETENTION_MONTHS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $this->purgeDashboardProjectionRatesFrom($purgeDate);
    }

    public function it_purges_daily_monthly_and_yearly_dashboard_rates_projection_at_the_last_day_of_the_year(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $purgeDate = new \DateTimeImmutable('2019-12-31');

        $dashboardRatesProjectionRepository->removeRates(Periodicity::daily(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d DAY', PurgeOutdatedData::RETENTION_DAYS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $dashboardRatesProjectionRepository->removeRates(Periodicity::monthly(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d MONTH', PurgeOutdatedData::RETENTION_MONTHS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $dashboardRatesProjectionRepository->removeRates(Periodicity::yearly(), Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d YEAR', PurgeOutdatedData::RETENTION_YEARS));
            return $purgeDate->format('Y-m-d') === $date->getDateTime()->format('Y-m-d');
        }))->shouldBeCalled();

        $this->purgeDashboardProjectionRatesFrom($purgeDate);
    }

    public function it_purges_outdated_product_axis_rates(ProductAxisRateRepositoryInterface $productAxisRateRepository)
    {
        $purgeDate = new \DateTimeImmutable('2019-12-31');

        $productAxisRateRepository->purgeUntil(Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d DAY', PurgeOutdatedData::RETENTION_DAYS));
            return $purgeDate->format('Y-m-d') === $date->format('Y-m-d');
        }));

        $this->purgeProductAxisRatesFrom($purgeDate);
    }

    public function it_purges_outdated_criterion_evaluations(CriterionEvaluationRepositoryInterface $criterionEvaluationRepository)
    {
        $purgeDate = new \DateTimeImmutable('2019-12-31');

        $criterionEvaluationRepository->purgeUntil(Argument::that(function ($date) use ($purgeDate) {
            $purgeDate = $purgeDate->modify(sprintf('-%d DAY', PurgeOutdatedData::RETENTION_DAYS));
            return $purgeDate->format('Y-m-d') === $date->format('Y-m-d');
        }));

        $this->purgeCriterionEvaluationsFrom($purgeDate);
    }
}
