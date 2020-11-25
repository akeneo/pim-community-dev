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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardPurgeDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardPurgeDateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PurgeOutdatedDataSpec extends ObjectBehavior
{
    public function let(
        DashboardScoresProjectionRepositoryInterface $dashboardRatesProjectionRepository,
        ProductAxisRateRepositoryInterface $productAxisRateRepository,
        ProductAxisRateRepositoryInterface $productModelAxisRateRepository
    ) {
        $this->beConstructedWith(
            $dashboardRatesProjectionRepository,
            $productAxisRateRepository,
            $productModelAxisRateRepository
        );
    }

    public function it_purges_dashboard_projection_rates(
        DashboardScoresProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $purgeDate = new \DateTimeImmutable('2020-03-27');
        $daily = TimePeriod::daily();

        $expectedPurgeDates = (new DashboardPurgeDateCollection())
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-20')))
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-19')))
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-18')))
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-17')))
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-16')))
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-15')))
            ->add($daily, new ConsolidationDate(new \DateTimeImmutable('2020-03-14')))
            ->add(TimePeriod::weekly(), new ConsolidationDate(new \DateTimeImmutable('2020-02-23')))
            ->add(TimePeriod::monthly(), new ConsolidationDate(new \DateTimeImmutable('2018-11-30')))
            ->add(TimePeriod::yearly(), new ConsolidationDate(new \DateTimeImmutable('2016-12-31')));

        $dashboardRatesProjectionRepository->purgeRates(Argument::that(function ($purgeDates) use ($expectedPurgeDates) {
            $purgeDates = $this->formatPurgeDatesForComparison($purgeDates);
            $expectedPurgeDates = $this->formatPurgeDatesForComparison($expectedPurgeDates);
            return $purgeDates == $expectedPurgeDates;
        }))->shouldBeCalled();

        $this->purgeDashboardProjectionRatesFrom($purgeDate);
    }

    public function it_purges_outdated_axis_rates(
        ProductAxisRateRepositoryInterface $productAxisRateRepository,
        ProductAxisRateRepositoryInterface $productModelAxisRateRepository
    ) {
        $purgeDate = new \DateTimeImmutable('2019-12-31');
        $limitDate = new \DateTimeImmutable('2019-12-24');

        $productAxisRateRepository->purgeUntil($limitDate)->shouldBeCalled();
        $productModelAxisRateRepository->purgeUntil($limitDate)->shouldBeCalled();

        $this->purgeProductAxisRatesFrom($purgeDate);
    }

    private function formatPurgeDatesForComparison(DashboardPurgeDateCollection $purgeDates): array
    {
        return array_map(function (DashboardPurgeDate $purgeDate) {
            return [
                'period' => strval($purgeDate->getPeriod()),
                'date' => $purgeDate->getDate()->format('Y-m-d')
            ];
        }, iterator_to_array($purgeDates));
    }
}
