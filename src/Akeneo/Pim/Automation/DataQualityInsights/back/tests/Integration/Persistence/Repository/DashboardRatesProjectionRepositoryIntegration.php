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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\DashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
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
        $result = $this->repository->findCatalogProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'));
        $this->assertNull($result);

        $this->insertCatalogRatesProjection($this->getRates());
        $result = $this->repository->findCatalogProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'));

        $this->assertEquals(
            new DashboardRates($this->getRates(), new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily')),
            $result
        );
    }

    public function test_it_finds_a_category_projection()
    {
        $result = $this->repository->findCategoryProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'), new CategoryCode('master'));
        $this->assertNull($result);

        $this->insertCategoryRatesProjection($this->getRates());
        $result = $this->repository->findCategoryProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'), new CategoryCode('master'));

        $this->assertEquals(
            new DashboardRates($this->getRates(), new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily')),
            $result
        );
    }

    public function test_it_finds_a_family_projection()
    {
        $result = $this->repository->findFamilyProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'), new FamilyCode('scanners'));
        $this->assertNull($result);

        $this->insertFamilyRatesProjection($this->getRates());
        $result = $this->repository->findFamilyProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'), new FamilyCode('scanners'));

        $this->assertEquals(
            new DashboardRates($this->getRates(), new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily')),
            $result
        );
    }

    public function test_it_removes_rates_for_a_given_periodicity_and_a_given_date()
    {
        $rates = [
            'daily' => [
                '2019-12-19' => [],
                '2019-12-20' => [],
                '2019-12-21' => [],
            ],
            'weekly' => [
                '2019-51' => [],
            ]
        ];

        $this->insertCatalogRatesProjection($rates);
        $this->insertCategoryRatesProjection($rates);

        $removingDate = new ConsolidationDate(new \DateTimeImmutable('2019-12-21'));
        $daily = Periodicity::daily();
        $this->assertCountRatesByDate(2, $daily, $removingDate);

        $this->repository->removeRates($daily, $removingDate);

        $this->assertCountRatesByDate(0, $daily, $removingDate);
        $this->assertCountRatesByDate(2, $daily, new ConsolidationDate(new \DateTimeImmutable('2019-12-20')));
        $this->assertCountRatesByDate(2, Periodicity::weekly(), $removingDate);
    }

    private function insertCatalogRatesProjection(array $rates): void
    {
        $sql = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection(type, code, rates)
VALUES (:type, :code, :rates)
SQL;

        $this->db->executequery($sql, [
            'type' => DashboardRatesProjectionRepository::TYPE_CATALOG_PROJECTION,
            'code' => "catalog",
            'rates' => json_encode($rates),
        ]);
    }

    private function insertCategoryRatesProjection(array $rates): void
    {
        $sql = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection(type, code, rates)
VALUES (:type, :code, :rates)
SQL;

        $this->db->executequery($sql, [
            'type' => DashboardRatesProjectionRepository::TYPE_CATEGORY_PROJECTION,
            'code' => "master",
            'rates' => json_encode($rates),
        ]);
    }

    private function insertFamilyRatesProjection(array $rates): void
    {
        $sql = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection(type, code, rates)
VALUES (:type, :code, :rates)
SQL;

        $this->db->executequery($sql, [
            'type' => DashboardRatesProjectionRepository::TYPE_FAMILY_PROJECTION,
            'code' => "scanners",
            'rates' => json_encode($rates),
        ]);
    }

    private function getRates(): array
    {
        return [
            "daily" => [
                "2019-12-17" => [
                    "consistency" => [
                        "ecommerce" => [
                            "en_US" => [
                                "1" => 12,
                                "2" => 28,
                                "3" => 10,
                                "4" => 50,
                                "5" => 10
                            ],
                            "fr_FR" => [
                                "1" => 30,
                                "2" => 10,
                                "3" => 20,
                                "4" => 20,
                                "5" => 20
                            ],
                        ],
                    ],
                    "enrichment" => [
                        "ecommerce" => [
                            "en_US" => [
                                "1" => 10,
                                "2" => 50,
                                "3" => 10,
                                "4" => 28,
                                "5" => 12
                            ],
                            "fr_FR" => [
                                "1" => 20,
                                "2" => 20,
                                "3" => 20,
                                "4" => 10,
                                "5" => 30
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertCountRatesByDate(int $expectedCount, Periodicity $periodicity, ConsolidationDate $date): void
    {
        $path = sprintf('\'$."%s"."%s"\'', $periodicity, $date->formatByPeriodicity($periodicity));

        $query = <<<SQL
SELECT COUNT(*) AS nb_rates 
FROM pimee_data_quality_insights_dashboard_rates_projection
WHERE JSON_CONTAINS_PATH(rates, 'one', $path)
SQL;

        $count = intval($this->db->executeQuery($query)->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }
}
