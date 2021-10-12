<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

final class DqiDashboardInstaller implements FixtureInstaller
{
    private Connection $db;

    private ConsolidateDashboardRates $consolidateDashboardRates;

    private DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository;

    public function __construct(
        Connection $dbConnection,
        ConsolidateDashboardRates $consolidateDashboardRates,
        DashboardScoresProjectionRepositoryInterface $dashboardScoresProjectionRepository
    ) {
        $this->db = $dbConnection;
        $this->consolidateDashboardRates = $consolidateDashboardRates;
        $this->dashboardScoresProjectionRepository = $dashboardScoresProjectionRepository;
    }

    public function install(): void
    {
        $now = new ConsolidationDate(new \DateTimeImmutable());

        $this->consolidateDashboardRates->consolidate($now);

        $idealCatalogScores = [
            1 => [ 'rank_1' => 20, 'rank_2' => 34, 'rank_3' => 21, 'rank_4' => 20, 'rank_5' => 5 ],
            2 => [ 'rank_1' => 13, 'rank_2' => 32, 'rank_3' => 23, 'rank_4' => 22, 'rank_5' => 10 ],
            3 => [ 'rank_1' => 13, 'rank_2' => 32, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 10 ],
            4 => [ 'rank_1' => 12, 'rank_2' => 31, 'rank_3' => 26, 'rank_4' => 25, 'rank_5' => 6 ],
            5 => [ 'rank_1' => 12, 'rank_2' => 33, 'rank_3' => 25, 'rank_4' => 23, 'rank_5' => 7 ],
            6 => [ 'rank_1' => 12, 'rank_2' => 33, 'rank_3' => 25, 'rank_4' => 22, 'rank_5' => 8 ],
            7 => [ 'rank_1' => 11, 'rank_2' => 32, 'rank_3' => 25, 'rank_4' => 22, 'rank_5' => 10 ],
        ];

        $idealFamilyScores = [
            1 => [ 'rank_1' => 90, 'rank_2' => 10, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
            2 => [ 'rank_1' => 85, 'rank_2' => 15, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0 ],
            3 => [ 'rank_1' => 70, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 0, 'rank_5' => 0 ],
            4 => [ 'rank_1' => 50, 'rank_2' => 15, 'rank_3' => 15, 'rank_4' => 10, 'rank_5' => 10 ],
            5 => [ 'rank_1' => 15, 'rank_2' => 20, 'rank_3' => 20, 'rank_4' => 25, 'rank_5' => 20 ],
            6 => [ 'rank_1' => 10, 'rank_2' => 10, 'rank_3' => 20, 'rank_4' => 30, 'rank_5' => 30 ],
            7 => [ 'rank_1' => 0, 'rank_2' => 0, 'rank_3' => 20, 'rank_4' => 40, 'rank_5' => 40 ],
        ];

        foreach ($this->getCurrentScoresProjections() as $scoresProjection) {
            $scores = json_decode($scoresProjection['scores'], true);
            $scoresOfTheDay = $scores['daily'][$now->format('Y-m-d')];

            if (empty($scoresOfTheDay)) {
                continue;
            }

            $numberOfProducts = $this->numberOfProducts($scoresOfTheDay);

            $projectionTypeAndCode = ['type' => null, 'code' => null];
            $idealScores = [];

            switch ($scoresProjection['type']) {
                case 'catalog':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::catalog();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::catalog();
                    $idealScores = $idealCatalogScores;
                    break;
                case 'category':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::category();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::category(new CategoryCode($scoresProjection['code']));
                    $idealScores = $idealCatalogScores;
                    break;
                case 'family':
                    $projectionTypeAndCode['type'] = DashboardProjectionType::family();
                    $projectionTypeAndCode['code'] = DashboardProjectionCode::family(new FamilyCode($scoresProjection['code']));
                    $idealScores = $idealFamilyScores;
                    break;
            }

            $ratesProjections = [];

            // Keep the real scores for the previous day to display them at j-1 (the scores of the current day are not displayed)
            $ratesProjections[] = new DashboardRatesProjection(
                $projectionTypeAndCode['type'],
                $projectionTypeAndCode['code'],
                $now->modify('-1 DAY'),
                new RanksDistributionCollection($scoresOfTheDay)
            );

            for ($i = 2; $i <= 7; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $now->modify(sprintf('-%d DAY', $i)),
                    new RanksDistributionCollection($this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, $i))
                );
            }

            for ($i = 1; $i <= 3; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $now->modify(sprintf('sunday %d weeks ago', $i)),
                    new RanksDistributionCollection($this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, $i * 2))
                );
            }

            $firstDayThisMonth = $now->modify('first day of this month');
            for ($i = 1; $i <= 6; $i++) {
                $ratesProjections[] = new DashboardRatesProjection(
                    $projectionTypeAndCode['type'],
                    $projectionTypeAndCode['code'],
                    $firstDayThisMonth->modify(sprintf('last day of %d months ago', $i)),
                    new RanksDistributionCollection($this->generateChaos($scoresOfTheDay, $numberOfProducts, $idealScores, $i))
                );
            }

            foreach ($ratesProjections as $ratesProjection) {
                $this->dashboardScoresProjectionRepository->save($ratesProjection);
            }
        }
    }

    private function getCurrentScoresProjections(): \Iterator
    {
        $statement = $this->db->executeQuery(
            'SELECT type, code, scores FROM pim_data_quality_insights_dashboard_scores_projection'
        );

        while ($scoresProjection = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $scoresProjection;
        }
    }

    private function generateChaos(array $scores, int $numberOfProducts, array $idealRates, int $day): array
    {
        foreach ($scores as $scopeCode => $locale) {
            foreach ($locale as $localeCode => $ranks) {
                foreach ($idealRates[$day] as $rankCode => $percentage) {
                    $scores[$scopeCode][$localeCode][$rankCode] = intval(round($numberOfProducts*$percentage/100)) + rand(1, intval(ceil($numberOfProducts*1/100)));
                }
            }
        }

        return $scores;
    }

    private function numberOfProducts(array $scores): int
    {
        $numberOfProducts = 0;

        foreach ($scores as $locale) {
            foreach ($locale as $ranks) {
                foreach ($ranks as $numberOfProductsEvaluated) {
                    $numberOfProducts += intval($numberOfProductsEvaluated);
                }

                return $numberOfProducts;
            }
        }

        return $numberOfProducts;
    }
}
