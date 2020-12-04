<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;

final class ConsolidateDashboardRates
{
    private GetRanksDistributionFromProductScoresQueryInterface $getRanksDistributionFromProductScoresQuery;

    private GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery;

    private GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery;

    private DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository;

    public function __construct(
        GetRanksDistributionFromProductScoresQueryInterface $getRanksDistributionFromProductScoresQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository
    ) {
        $this->getRanksDistributionFromProductScoresQuery = $getRanksDistributionFromProductScoresQuery;
        $this->getAllCategoryCodesQuery = $getAllCategoryCodesQuery;
        $this->getAllFamilyCodesQuery = $getAllFamilyCodesQuery;
        $this->dashboardScoresProjectionRepository = $dashboardScoresProjectionRepository;
    }

    public function consolidate(ConsolidationDate $day): void
    {
        $this->consolidateWholeCatalog($day);
        $this->consolidateFamilies($day);
        $this->consolidateCategories($day);
    }

    private function consolidateWholeCatalog(ConsolidationDate $day): void
    {
        $catalogRanks = $this->getRanksDistributionFromProductScoresQuery->forWholeCatalog($day->getDateTime());

        $dashBoardRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $day,
            $catalogRanks
        );

        $this->dashboardScoresProjectionRepository->save($dashBoardRatesProjection);
    }

    private function consolidateFamilies(ConsolidationDate $day): void
    {
        $dashboardFamily = DashboardProjectionType::family();
        $familyCodes = $this->getAllFamilyCodesQuery->execute();

        foreach ($familyCodes as $familyCode) {
            $familyRanks = $this->getRanksDistributionFromProductScoresQuery->byFamily($familyCode, $day->getDateTime());
            $dashBoardRatesProjection = new DashboardRatesProjection(
                $dashboardFamily,
                DashboardProjectionCode::family($familyCode),
                $day,
                $familyRanks
            );

            $this->dashboardScoresProjectionRepository->save($dashBoardRatesProjection);
        }
    }

    private function consolidateCategories(ConsolidationDate $day): void
    {
        $dashboardCategory = DashboardProjectionType::category();
        $categoryCodes = $this->getAllCategoryCodesQuery->execute();

        foreach ($categoryCodes as $categoryCode) {
            $categoryRanks = $this->getRanksDistributionFromProductScoresQuery->byCategory($categoryCode, $day->getDateTime());
            $dashBoardRatesProjection = new DashboardRatesProjection(
                $dashboardCategory,
                DashboardProjectionCode::category($categoryCode),
                $day,
                $categoryRanks
            );

            $this->dashboardScoresProjectionRepository->save($dashBoardRatesProjection);
        }
    }
}
