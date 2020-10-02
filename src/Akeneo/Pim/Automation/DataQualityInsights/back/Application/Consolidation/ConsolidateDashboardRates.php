<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductAxisRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;

final class ConsolidateDashboardRates
{
    /** @var GetRanksDistributionFromProductAxisRatesQueryInterface */
    private $getRanksDistributionFromProductAxisRatesQuery;

    /** @var GetAllCategoryCodesQueryInterface */
    private $getAllCategoryCodesQuery;

    /** @var GetAllFamilyCodesQueryInterface */
    private $getAllFamilyCodesQuery;

    /** @var DashboardRatesProjectionRepositoryInterface */
    private $dashboardRatesProjectionRepository;

    public function __construct(
        GetRanksDistributionFromProductAxisRatesQueryInterface $getRanksDistributionFromProductAxisRatesQuery,
        GetAllCategoryCodesQueryInterface $getAllCategoryCodesQuery,
        GetAllFamilyCodesQueryInterface $getAllFamilyCodesQuery,
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository
    ) {
        $this->getRanksDistributionFromProductAxisRatesQuery = $getRanksDistributionFromProductAxisRatesQuery;
        $this->getAllCategoryCodesQuery = $getAllCategoryCodesQuery;
        $this->getAllFamilyCodesQuery = $getAllFamilyCodesQuery;
        $this->dashboardRatesProjectionRepository = $dashboardRatesProjectionRepository;
    }

    public function consolidate(ConsolidationDate $day): void
    {
        $this->consolidateWholeCatalog($day);
        $this->consolidateFamilies($day);
        $this->consolidateCategories($day);
    }

    private function consolidateWholeCatalog(ConsolidationDate $day): void
    {
        $catalogRanks = $this->getRanksDistributionFromProductAxisRatesQuery->forWholeCatalog($day->getDateTime());

        $dashBoardRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $day,
            $catalogRanks
        );

        $this->dashboardRatesProjectionRepository->save($dashBoardRatesProjection);
    }

    private function consolidateFamilies(ConsolidationDate $day): void
    {
        $dashboardFamily = DashboardProjectionType::family();
        $familyCodes = $this->getAllFamilyCodesQuery->execute();

        foreach ($familyCodes as $familyCode) {
            $familyRanks = $this->getRanksDistributionFromProductAxisRatesQuery->byFamily($familyCode, $day->getDateTime());
            $dashBoardRatesProjection = new DashboardRatesProjection(
                $dashboardFamily,
                DashboardProjectionCode::family($familyCode),
                $day,
                $familyRanks
            );

            $this->dashboardRatesProjectionRepository->save($dashBoardRatesProjection);
        }
    }

    private function consolidateCategories(ConsolidationDate $day): void
    {
        $dashboardCategory = DashboardProjectionType::category();
        $categoryCodes = $this->getAllCategoryCodesQuery->execute();

        foreach ($categoryCodes as $categoryCode) {
            $categoryRanks = $this->getRanksDistributionFromProductAxisRatesQuery->byCategory($categoryCode, $day->getDateTime());
            $dashBoardRatesProjection = new DashboardRatesProjection(
                $dashboardCategory,
                DashboardProjectionCode::category($categoryCode),
                $day,
                $categoryRanks
            );

            $this->dashboardRatesProjectionRepository->save($dashBoardRatesProjection);
        }
    }
}
