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

        $this->insertCatalogRatesProjection();
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

        $this->insertCategoryRatesProjection();
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

        $this->insertFamilyRatesProjection();
        $result = $this->repository->findFamilyProjection(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily'), new FamilyCode('scanners'));

        $this->assertEquals(
            new DashboardRates($this->getRates(), new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Periodicity('daily')),
            $result
        );
    }

    private function insertCatalogRatesProjection(): void
    {
        $sql = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection(type, code, rates)
VALUES (:type, :code, :rates)
SQL;

        $stmt = $this->db->executequery($sql, [
            'type' => DashboardRatesProjectionRepository::TYPE_CATALOG_PROJECTION,
            'code' => "catalog",
            'rates' => json_encode($this->getRates()),
        ]);
    }

    private function insertCategoryRatesProjection(): void
    {
        $sql = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection(type, code, rates)
VALUES (:type, :code, :rates)
SQL;

        $stmt = $this->db->executequery($sql, [
            'type' => DashboardRatesProjectionRepository::TYPE_CATEGORY_PROJECTION,
            'code' => "master",
            'rates' => json_encode($this->getRates()),
        ]);
    }

    private function insertFamilyRatesProjection(): void
    {
        $sql = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection(type, code, rates)
VALUES (:type, :code, :rates)
SQL;

        $stmt = $this->db->executequery($sql, [
            'type' => DashboardRatesProjectionRepository::TYPE_FAMILY_PROJECTION,
            'code' => "scanners",
            'rates' => json_encode($this->getRates()),
        ]);
    }

    private function getRates()
    {
        return [
            "consistency" => [
                "daily" => [
                    "2019-12-17" => [
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
                            ]
                        ]
                    ],
                ]
            ],
            "enrichment" => [
                "daily" => [
                    "2019-12-17" => [
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
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
