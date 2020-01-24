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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\DashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardRatesProjectionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class DashboardRatesProjectionRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var DashboardRatesProjectionRepositoryInterface */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->repository = $this->get(DashboardRatesProjectionRepository::class);
    }

    public function test_it_finds_catalog_projection()
    {
        $result = $this->repository->findCatalogProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));
        $this->assertNull($result);

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertCatalogRatesProjection($consolidationDate, $ranksDistributionCollection);
        $result = $this->repository->findCatalogProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $expectedDashboardRates = new DashboardRates([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray()
            ]
        ], new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $this->assertEquals($expectedDashboardRates->toArray(), $result->toArray());
    }

    public function test_it_finds_a_category_projection()
    {
        $result = $this->repository->findCategoryProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new CategoryCode('master'));
        $this->assertNull($result);

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertCategoryRatesProjection($consolidationDate, $ranksDistributionCollection);
        $result = $this->repository->findCategoryProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new CategoryCode('master'));

        $expectedDashboardRates = new DashboardRates([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray()
            ]
        ], new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $this->assertEquals($expectedDashboardRates->toArray(), $result->toArray());
    }

    public function test_it_finds_a_family_projection()
    {
        $result = $this->repository->findFamilyProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new FamilyCode('scanners'));
        $this->assertNull($result);

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertFamilyRatesProjection($consolidationDate, $ranksDistributionCollection);
        $result = $this->repository->findFamilyProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new FamilyCode('scanners'));

        $expectedDashboardRates = new DashboardRates([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray()
            ]
        ], new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $this->assertEquals($expectedDashboardRates->toArray(), $result->toArray());
    }

    public function test_it_removes_rates_for_a_given_time_period_and_a_given_date()
    {
        $commonDay = new ConsolidationDate(new \DateTimeImmutable('2019-12-19'));
        $lastDayOfWeek = new ConsolidationDate(new \DateTimeImmutable('2019-12-22'));
        $lastDayOfMonth = new ConsolidationDate(new \DateTimeImmutable('2019-12-31'));

        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertCatalogRatesProjection($commonDay, $ranksDistributionCollection);
        $this->insertCatalogRatesProjection($lastDayOfWeek, $ranksDistributionCollection);
        $this->insertCatalogRatesProjection($lastDayOfMonth, $ranksDistributionCollection);

        $this->insertCategoryRatesProjection($commonDay, $ranksDistributionCollection);
        $this->insertCategoryRatesProjection($lastDayOfWeek, $ranksDistributionCollection);
        $this->insertCategoryRatesProjection($lastDayOfMonth, $ranksDistributionCollection);

        $daily = TimePeriod::daily();
        $monthly = TimePeriod::monthly();
        $this->assertCountRatesByDate(2, $daily, $lastDayOfMonth);
        $this->assertCountRatesByDate(2, $monthly, $lastDayOfMonth);

        $this->repository->removeRates($daily, $lastDayOfMonth);

        $this->assertCountRatesByDate(0, $daily, $lastDayOfMonth);
        $this->assertCountRatesByDate(2, $daily, $commonDay);
        $this->assertCountRatesByDate(2, TimePeriod::weekly(), $lastDayOfWeek);
        $this->assertCountRatesByDate(2, $monthly, $lastDayOfMonth);

        $this->repository->removeRates($monthly, $lastDayOfMonth);
        $this->assertCountRatesByDate(0, $monthly, $lastDayOfMonth);
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

    private function insertFamilyRatesProjection(ConsolidationDate $consolidationDate, RanksDistributionCollection $ranksDistributionCollection): void
    {
        $dashboardRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family(new FamilyCode('scanners')),
            $consolidationDate,
            $ranksDistributionCollection
        );

        $this->repository->save($dashboardRatesProjection);
    }

    private function getRanksDistributionCollection(): RanksDistributionCollection
    {
        return new RanksDistributionCollection([
            "consistency" => [
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
            ],
            "enrichment" => [
                "ecommerce" => [
                    "en_US" => [
                        "rank_1" => 10,
                        "rank_2" => 50,
                        "rank_3" => 10,
                        "rank_4" => 28,
                        "rank_5" => 12
                    ],
                    "fr_FR" => [
                        "rank_1" => 20,
                        "rank_2" => 20,
                        "rank_3" => 20,
                        "rank_4" => 10,
                        "rank_5" => 30
                    ],
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
FROM pimee_data_quality_insights_dashboard_rates_projection
WHERE JSON_CONTAINS_PATH(rates, 'one', $path)
SQL;

        $count = intval($this->db->executeQuery($query)->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }
}
