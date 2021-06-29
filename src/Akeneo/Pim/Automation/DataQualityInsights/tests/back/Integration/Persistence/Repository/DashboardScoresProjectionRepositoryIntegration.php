<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardPurgeDateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardScoresProjectionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class DashboardScoresProjectionRepositoryIntegration extends TestCase
{
    private Connection $db;

    private DashboardScoresProjectionRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->repository = $this->get(DashboardScoresProjectionRepository::class);
    }

    public function test_it_purges_rates_for_given_dates()
    {
        $commonDay = new ConsolidationDate(new \DateTimeImmutable('2019-12-19'));
        $dayToPurge = new ConsolidationDate(new \DateTimeImmutable('2019-11-17'));
        $weekToPurge = new ConsolidationDate(new \DateTimeImmutable('2019-12-01'));
        $lastDayOfMonth = new ConsolidationDate(new \DateTimeImmutable('2019-12-31'));

        $daily = TimePeriod::daily();
        $monthly = TimePeriod::monthly();
        $weekly = TimePeriod::weekly();

        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertCatalogRatesProjection($commonDay, $ranksDistributionCollection);
        $this->insertCatalogRatesProjection($dayToPurge, $ranksDistributionCollection);
        $this->insertCatalogRatesProjection($weekToPurge, $ranksDistributionCollection);
        $this->insertCatalogRatesProjection($lastDayOfMonth, $ranksDistributionCollection);
        $this->insertCategoryRatesProjection($dayToPurge, $ranksDistributionCollection);
        $this->insertCategoryRatesProjection($commonDay, $ranksDistributionCollection);
        $this->insertCategoryRatesProjection($lastDayOfMonth, $ranksDistributionCollection);

        $this->assertCountRatesByDate(2, $daily, $dayToPurge);
        $this->assertCountRatesByDate(1, $weekly, $weekToPurge);
        $this->assertCountRatesByDate(2, $monthly, $lastDayOfMonth);

        $datesToPurge = (new DashboardPurgeDateCollection())
            ->add($daily, $dayToPurge)
            ->add($weekly, $weekToPurge)
            ->add($monthly, $lastDayOfMonth);

        $this->repository->purgeRates($datesToPurge);

        $this->assertCountRatesByDate(2, $daily, $lastDayOfMonth);
        $this->assertCountRatesByDate(2, $daily, $commonDay);

        $this->assertCountRatesByDate(0, $daily, $dayToPurge);
        $this->assertCountRatesByDate(0, $weekly, $weekToPurge);
        $this->assertCountRatesByDate(0, $monthly, $lastDayOfMonth);
    }

    public function test_it_does_not_save_outdated_average_ranks()
    {
        $youngestRanksDistributionCollection = new RanksDistributionCollection([
            "ecommerce" => ["en_US" => ["rank_4" => 50]]
        ]);
        $youngestAverageRanks = [
            "ecommerce" => ["en_US" => "rank_4"],
        ];
        $youngestConsolidationDate = new ConsolidationDate(new \DateTimeImmutable('2019-12-19 12:34:41'));

        $this->insertCatalogRatesProjection($youngestConsolidationDate, $youngestRanksDistributionCollection);
        $this->assertCatalogAverageRanksEquals($youngestAverageRanks);

        $olderRanksDistributionCollection = new RanksDistributionCollection([
            "ecommerce" => ["en_US" => ["rank_1" => 50]],
        ]);
        $olderConsolidationDate = $youngestConsolidationDate->modify('-1 second');

        $this->insertCatalogRatesProjection($olderConsolidationDate, $olderRanksDistributionCollection);
        $this->assertCatalogAverageRanksEquals($youngestAverageRanks);

        $youngerRanksDistributionCollection = new RanksDistributionCollection([
            "ecommerce" => ["en_US" => ["rank_3" => 50]],
        ]);
        $youngerAverageRanks = [
            "ecommerce" => ["en_US" => "rank_3"],
        ];
        $youngerConsolidationDate = $youngestConsolidationDate->modify('+1 second');

        $this->insertCatalogRatesProjection($youngerConsolidationDate, $youngerRanksDistributionCollection);
        $this->assertCatalogAverageRanksEquals($youngerAverageRanks);
    }

    private function insertCatalogRatesProjection(ConsolidationDate $consolidationDate, RanksDistributionCollection $ranksDistributionCollection): void
    {
        $dashboardRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->repository->save($dashboardRatesProjection);
    }

    private function insertCategoryRatesProjection(ConsolidationDate $consolidationDate, RanksDistributionCollection $ranksDistributionCollection): void
    {
        $dashboardRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category(new CategoryCode('master')),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->repository->save($dashboardRatesProjection);
    }

    private function getRanksDistributionCollection(): RanksDistributionCollection
    {
        return new RanksDistributionCollection([
            "ecommerce" => [
                "en_US" => [
                    "rank_1" => 12,
                    "rank_2" => 28,
                    "rank_3" => 10,
                    "rank_4" => 50,
                    "rank_5" => 10
                ],
                "fr_FR" => [
                    "rank_1" => 30,
                    "rank_2" => 10,
                    "rank_3" => 20,
                    "rank_4" => 20,
                    "rank_5" => 20
                ],
            ],
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertCountRatesByDate(int $expectedCount, TimePeriod $timePeriod, ConsolidationDate $date): void
    {
        $path = sprintf('\'$."%s"."%s"\'', $timePeriod, $date->format());

        $query = <<<SQL
SELECT COUNT(*) AS nb_rates
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE JSON_CONTAINS_PATH(scores, 'one', $path)
SQL;

        $count = intval($this->db->executeQuery($query)->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }

    private function assertCatalogAverageRanksEquals(array $expectedAverageRanks): void
    {
        $query = <<<SQL
SELECT JSON_EXTRACT(scores, '$.average_ranks') as average_ranks
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE type = :type AND code = :code
SQL;

        $stmt = $this->db->executeQuery($query, [
            'type' => DashboardProjectionType::CATALOG,
            'code' => DashboardProjectionCode::CATALOG
        ]);

        $averageRanks = json_decode($stmt->fetchColumn(), true);

        $this->assertEquals($expectedAverageRanks, $averageRanks);
    }
}
