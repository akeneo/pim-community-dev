<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\DashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard\GetDashboardScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardScoresProjectionRepository;
use Akeneo\Test\Integration\TestCase;

final class GetDashboardScoresQueryIntegration extends TestCase
{
    private DashboardScoresProjectionRepositoryInterface $repository;

    private GetDashboardScoresQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get(DashboardScoresProjectionRepository::class);
        $this->query = $this->get(GetDashboardScoresQuery::class);
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
