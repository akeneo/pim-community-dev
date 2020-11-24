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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard\GetAverageRanksQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardRatesProjectionRepository;
use Akeneo\Test\Integration\TestCase;

final class GetAverageRanksQueryIntegration extends TestCase
{
    /** @var GetAverageRanksQuery */
    private $query;

    /** @var DashboardRatesProjectionRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAverageRanksQuery::class);
        $this->repository = $this->get(DashboardRatesProjectionRepository::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_average_ranks_by_families()
    {
        $familyACode = new FamilyCode('scanners');
        $familyBCode = new FamilyCode('mugs');
        $familyCCode = new FamilyCode('webcams');
        $familyWithoutRates = new FamilyCode('shoes');

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2019-12-19'));

        $ranksDistributionFamilyA = new RanksDistributionCollection([
            'ecommerce' => [
                'en_US' => ['rank_4' => 50],
                'fr_FR' => ['rank_3' => 40],
            ],
            'mobile' => [
                'en_US' => ['rank_2' => 50],
            ],
        ]);

        $ranksDistributionFamilyB = new RanksDistributionCollection([
            'ecommerce' => [
                'en_US' => ['rank_1' => 11],
                'fr_FR' => ['rank_2' => 14],
            ],
        ]);

        $ranksDistributionFamilyC = new RanksDistributionCollection([
            'ecommerce' => [
                'en_US' => ['rank_3' => 21],
            ],
        ]);

        $this->repository->save(new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyACode),
            $consolidationDate,
            $ranksDistributionFamilyA
        ));
        $this->repository->save(new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyBCode),
            $consolidationDate,
            $ranksDistributionFamilyB
        ));
        $this->repository->save(new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyCCode),
            $consolidationDate,
            $ranksDistributionFamilyC
        ));

        $expectedAverageRanks = [
            strval($familyACode) => Rank::fromString('rank_4'),
            strval($familyBCode) => Rank::fromString('rank_1'),
            strval($familyWithoutRates) => null,
        ];

        $averageRanks = $this->query->byFamilies(new ChannelCode('ecommerce'), new LocaleCode('en_US'), [$familyACode, $familyBCode, $familyWithoutRates]);

        $this->assertEquals($expectedAverageRanks, $averageRanks);
    }

    public function test_it_returns_average_ranks_by_categories()
    {
        $categoryACode = new CategoryCode('camcorders');
        $categoryBCode = new CategoryCode('scanners');
        $categoryCCode = new CategoryCode('webcams');
        $categoryWithoutRates = new CategoryCode('shoes');

        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2019-12-19'));

        $ranksDistributionCategoryA = new RanksDistributionCollection([
            'ecommerce' => [
                'en_US' => ['rank_4' => 50],
                'fr_FR' => ['rank_3' => 40],
            ],
            'mobile' => [
                'en_US' => ['rank_2' => 50],
            ],
        ]);

        $ranksDistributionCategoryB = new RanksDistributionCollection([
            'ecommerce' => [
                'en_US' => ['rank_1' => 11],
                'fr_FR' => ['rank_2' => 14],
            ],
            'mobile' => [
                'en_US' => ['rank_2' => 6],
            ],
        ]);

        $ranksDistributionCategoryC = new RanksDistributionCollection([
            'ecommerce' => [
                'en_US' => ['rank_3' => 21],
            ],
        ]);

        $this->repository->save(new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($categoryACode),
            $consolidationDate,
            $ranksDistributionCategoryA
        ));
        $this->repository->save(new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($categoryBCode),
            $consolidationDate,
            $ranksDistributionCategoryB
        ));
        $this->repository->save(new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($categoryCCode),
            $consolidationDate,
            $ranksDistributionCategoryC
        ));

        $expectedAverageRanks = [
            strval($categoryACode) => Rank::fromString('rank_4'),
            strval($categoryBCode) => Rank::fromString('rank_1'),
            strval($categoryWithoutRates) => null,
        ];

        $averageRanks = $this->query->byCategories(new ChannelCode('ecommerce'), new LocaleCode('en_US'), [$categoryACode, $categoryBCode, $categoryWithoutRates]);

        $this->assertEquals($expectedAverageRanks, $averageRanks);
    }
}
