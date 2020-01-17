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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetRanksDistributionFromProductAxisRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
use PhpSpec\ObjectBehavior;

class ConsolidateDashboardRatesSpec extends ObjectBehavior
{
    public function let(
        GetRanksDistributionFromProductAxisRatesQueryInterface $getRanksDistributionFromProductAxisRatesQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $this->beConstructedWith($getRanksDistributionFromProductAxisRatesQuery, $getAllCategoryCodesQuery, $getAllFamilyCodesQuery, $dashboardRatesProjectionRepository);
    }

    public function it_consolidates_the_dashboard_rates_for_a_last_day_of_a_week(
        GetRanksDistributionFromProductAxisRatesQueryInterface $getRanksDistributionFromProductAxisRatesQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $dateTime = new \DateTimeImmutable('2020-01-19');
        $consolidationDate = new ConsolidationDate($dateTime);

        $catalogRanks = $this->buildRandomRanksDistribution();
        $getRanksDistributionFromProductAxisRatesQuery->forWholeCatalog($dateTime)->willReturn($catalogRanks);

        $catalogRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            [
                Periodicity::DAILY => ['2020-01-19' => $catalogRanks],
                Periodicity::WEEKLY => ['2020-01-19' => $catalogRanks]
            ]
        );

        $dashboardRatesProjectionRepository->save($catalogRatesProjection)->shouldBeCalled();

        $familyMugsCode = new FamilyCode('mugs');
        $familyWebcamsCode = new FamilyCode('webcams');
        $familyMugsRanks = $this->buildRandomRanksDistribution();
        $familyWebcamsRanks = $this->buildRandomRanksDistribution();

        $getAllFamilyCodesQuery->execute()->willReturn([$familyMugsCode, $familyWebcamsCode]);
        $getRanksDistributionFromProductAxisRatesQuery->byFamily($familyMugsCode, $dateTime)->willReturn($familyMugsRanks);
        $getRanksDistributionFromProductAxisRatesQuery->byFamily($familyWebcamsCode, $dateTime)->willReturn($familyWebcamsRanks);

        $familyMugsRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyMugsCode),
            [
                Periodicity::DAILY => ['2020-01-19' => $familyMugsRanks],
                Periodicity::WEEKLY => ['2020-01-19' => $familyMugsRanks],
            ]
        );
        $familyWebcamsRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyWebcamsCode),
            [
                Periodicity::DAILY => ['2020-01-19' => $familyWebcamsRanks],
                Periodicity::WEEKLY => ['2020-01-19' => $familyWebcamsRanks],
            ]
        );

        $dashboardRatesProjectionRepository->save($familyMugsRatesProjection)->shouldBeCalled();
        $dashboardRatesProjectionRepository->save($familyWebcamsRatesProjection)->shouldBeCalled();

        $getAllCategoryCodesQuery->execute()->willReturn([]);

        $this->consolidate($consolidationDate);
    }

    public function it_consolidates_the_dashboard_rates_for_a_last_day_of_a_year(
        GetRanksDistributionFromProductAxisRatesQueryInterface $getRanksDistributionFromProductAxisRatesQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $dateTime = new \DateTimeImmutable('2019-12-31');
        $consolidationDate = new ConsolidationDate($dateTime);

        $catalogRanks = $this->buildRandomRanksDistribution();
        $getRanksDistributionFromProductAxisRatesQuery->forWholeCatalog($dateTime)->willReturn($catalogRanks);

        $catalogRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            [
                Periodicity::DAILY => ['2019-12-31' => $catalogRanks],
                Periodicity::MONTHLY => ['2019-12-31' => $catalogRanks],
                Periodicity::YEARLY => ['2019-12-31' => $catalogRanks],
            ]
        );

        $dashboardRatesProjectionRepository->save($catalogRatesProjection)->shouldBeCalled();

        $getAllFamilyCodesQuery->execute()->willReturn([]);

        $category1Code = new CategoryCode('category_1');
        $category2Code = new CategoryCode('category_2');
        $category1Ranks = $this->buildRandomRanksDistribution();
        $category2Ranks = $this->buildRandomRanksDistribution();

        $getAllCategoryCodesQuery->execute()->willReturn([$category1Code, $category2Code]);
        $getRanksDistributionFromProductAxisRatesQuery->byCategory($category1Code, $dateTime)->willReturn($category1Ranks);
        $getRanksDistributionFromProductAxisRatesQuery->byCategory($category2Code, $dateTime)->willReturn($category2Ranks);

        $category1RatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($category1Code),
            [
                Periodicity::DAILY => ['2019-12-31' => $category1Ranks],
                Periodicity::MONTHLY => ['2019-12-31' => $category1Ranks],
                Periodicity::YEARLY => ['2019-12-31' => $category1Ranks],
            ]
        );
        $category2RatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($category2Code),
            [
                Periodicity::DAILY => ['2019-12-31' => $category2Ranks],
                Periodicity::MONTHLY => ['2019-12-31' => $category2Ranks],
                Periodicity::YEARLY => ['2019-12-31' => $category2Ranks],
            ]
        );

        $dashboardRatesProjectionRepository->save($category1RatesProjection)->shouldBeCalled();
        $dashboardRatesProjectionRepository->save($category2RatesProjection)->shouldBeCalled();

        $this->consolidate($consolidationDate);
    }

    private function buildRandomRanksDistribution(): array
    {
        return [
            "consistency" => [
                "ecommerce" => [
                    "en_US" => [
                        "1" => rand(1, 100),
                        "2" => rand(1, 100),
                        "3" => rand(1, 100),
                        "4" => rand(1, 100),
                        "5" => rand(1, 100)
                    ],
                ],
            ],
            "enrichment" => [
                "ecommerce" => [
                    "en_US" => [
                        "1" => rand(1, 100),
                        "2" => rand(1, 100),
                        "3" => rand(1, 100),
                        "4" => rand(1, 100),
                        "5" => rand(1, 100)
                    ],
                ],
            ],
        ];
    }
}
