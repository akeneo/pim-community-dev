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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Dashboard;

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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard\GetDashboardRatesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardRatesProjectionRepository;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class GetDashboardRatesQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var DashboardRatesProjectionRepositoryInterface */
    private $repository;

    /** @var GetDashboardRatesQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->repository = $this->get(DashboardRatesProjectionRepository::class);
        $this->query = $this->get(GetDashboardRatesQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_finds_catalog_projection()
    {
        $result = $this->query->byCatalog(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));
        $this->assertNull($result);

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertCatalogRatesProjection($consolidationDate, $ranksDistributionCollection);
        $result = $this->query->byCatalog(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $expectedDashboardRates = new DashboardRates([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray()
            ]
        ], new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $this->assertEquals($expectedDashboardRates->toArray(), $result->toArray());
    }

    public function test_it_finds_a_category_projection()
    {
        $result = $this->query->byCategory(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new CategoryCode('master'));
        $this->assertNull($result);

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertCategoryRatesProjection($consolidationDate, $ranksDistributionCollection);
        $result = $this->query->byCategory(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new CategoryCode('master'));

        $expectedDashboardRates = new DashboardRates([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray()
            ]
        ], new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $this->assertEquals($expectedDashboardRates->toArray(), $result->toArray());
    }

    public function test_it_finds_a_family_projection()
    {
        $result = $this->query->byFamily(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new FamilyCode('scanners'));
        $this->assertNull($result);

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->getRanksDistributionCollection();
        $this->insertFamilyRatesProjection($consolidationDate, $ranksDistributionCollection);
        $result = $this->query->byFamily(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'), new FamilyCode('scanners'));

        $expectedDashboardRates = new DashboardRates([
            TimePeriod::DAILY => [
                $consolidationDate->format() => $ranksDistributionCollection->toArray()
            ]
        ], new ChannelCode('ecommerce'), new LocaleCode('en_US'), new TimePeriod('daily'));

        $this->assertEquals($expectedDashboardRates->toArray(), $result->toArray());
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
}
