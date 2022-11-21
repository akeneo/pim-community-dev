<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

class ConsolidateDashboardRatesSpec extends ObjectBehavior
{
    public function let(
        GetRanksDistributionFromProductScoresQueryInterface $getRanksDistributionFromProductAxisRatesQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardScoresProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $this->beConstructedWith($getRanksDistributionFromProductAxisRatesQuery, $getAllCategoryCodesQuery, $getAllFamilyCodesQuery, $dashboardRatesProjectionRepository);
    }

    public function it_consolidates_the_dashboard_rates(
        GetRanksDistributionFromProductScoresQueryInterface $getRanksDistributionFromProductAxisRatesQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardScoresProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $dateTime = new \DateTimeImmutable('2020-01-19');
        $consolidationDate = new ConsolidationDate($dateTime);

        $catalogRanks = $this->buildRandomRanksDistributionCollection();
        $getRanksDistributionFromProductAxisRatesQuery->forWholeCatalog($dateTime)->willReturn($catalogRanks);

        $catalogRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $catalogRanks
        );

        $dashboardRatesProjectionRepository->save($catalogRatesProjection)->shouldBeCalled();

        $familyMugsCode = new FamilyCode('mugs');
        $familyWebcamsCode = new FamilyCode('webcams');
        $familyMugsRanks = $this->buildRandomRanksDistributionCollection();
        $familyWebcamsRanks = $this->buildRandomRanksDistributionCollection();

        $getAllFamilyCodesQuery->execute()->willReturn([$familyMugsCode, $familyWebcamsCode]);
        $getRanksDistributionFromProductAxisRatesQuery->byFamily($familyMugsCode, $dateTime)->willReturn($familyMugsRanks);
        $getRanksDistributionFromProductAxisRatesQuery->byFamily($familyWebcamsCode, $dateTime)->willReturn($familyWebcamsRanks);

        $familyMugsRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyMugsCode),
            $consolidationDate,
            $familyMugsRanks
        );
        $familyWebcamsRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyWebcamsCode),
            $consolidationDate,
            $familyWebcamsRanks
        );

        $dashboardRatesProjectionRepository->save($familyMugsRatesProjection)->shouldBeCalled();
        $dashboardRatesProjectionRepository->save($familyWebcamsRatesProjection)->shouldBeCalled();

        $category1Code = new CategoryCode('category_1');
        $category2Code = new CategoryCode('category_2');
        $category1Ranks = $this->buildRandomRanksDistributionCollection();
        $category2Ranks = $this->buildRandomRanksDistributionCollection();

        $getAllCategoryCodesQuery->execute()->willReturn([$category1Code, $category2Code]);
        $getRanksDistributionFromProductAxisRatesQuery->byCategory($category1Code, $dateTime)->willReturn($category1Ranks);
        $getRanksDistributionFromProductAxisRatesQuery->byCategory($category2Code, $dateTime)->willReturn($category2Ranks);

        $category1RatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($category1Code),
            $consolidationDate,
            $category1Ranks
        );
        $category2RatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($category2Code),
            $consolidationDate,
            $category2Ranks
        );

        $dashboardRatesProjectionRepository->save($category1RatesProjection)->shouldBeCalled();
        $dashboardRatesProjectionRepository->save($category2RatesProjection)->shouldBeCalled();

        $this->consolidate($consolidationDate);
    }

    private function buildRandomRanksDistributionCollection(): RanksDistributionCollection
    {
        return new RanksDistributionCollection([
            "ecommerce" => [
                "en_US" => [
                    "rank_1" => rand(1, 100),
                    "rank_2" => rand(1, 100),
                    "rank_3" => rand(1, 100),
                    "rank_4" => rand(1, 100),
                    "rank_5" => rand(1, 100)
                ],
            ],
        ]);
    }
}
