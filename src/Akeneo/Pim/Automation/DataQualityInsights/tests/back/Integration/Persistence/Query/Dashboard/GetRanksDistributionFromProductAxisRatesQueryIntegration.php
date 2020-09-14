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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard\GetRanksDistributionFromProductAxisRatesQuery;
use Akeneo\Test\Integration\TestCase;

final class GetRanksDistributionFromProductAxisRatesQueryIntegration extends TestCase
{
    private const CONSOLIDATION_DATE = '2020-01-15';

    /** @var int */
    private $lastProductId ;

    /** @var GetRanksDistributionFromProductAxisRatesQuery */
    private $query;

    /** @var ProductAxisRateRepositoryInterface */
    private $productAxisRateRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productAxisRateRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_axis_rate');
        $this->query = $this->get(GetRanksDistributionFromProductAxisRatesQuery::class);
        $this->lastProductId = 0;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_ranks_distribution_for_whole_catalog()
    {
        $this->givenProductsByRankAndAxisForWholeCatalog(3, 1, 'enrichment');
        $this->givenProductsByRankAndAxisForWholeCatalog(9, 2, 'enrichment');
        $this->givenProductsByRankAndAxisForWholeCatalog(7, 5, 'enrichment');

        $this->givenProductsByRankAndAxisForWholeCatalog(2, 2, 'consistency');
        $this->givenProductsByRankAndAxisForWholeCatalog(4, 3, 'consistency');

        $ranksDistribution = $this->query->forWholeCatalog(new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 3,
                        'rank_2' => 9,
                        'rank_3' => 0,
                        'rank_4' => 0,
                        'rank_5' => 7,
                    ]
                ]
            ],
            'consistency' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 0,
                        'rank_2' => 2,
                        'rank_3' => 4,
                        'rank_4' => 0,
                        'rank_5' => 0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedRanksDistribution, $ranksDistribution->toArray());
    }

    public function test_it_returns_ranks_distribution_by_family()
    {
        $this->givenFamily('mugs');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 8, 1, 'enrichment');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 11, 3, 'enrichment');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 6, 4, 'enrichment');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 7, 4, 'consistency');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 13, 5, 'consistency');

        $this->givenFamily('webcams');
        $this->givenProductsWithFamilyByRankAndAxis('webcams', 6, 1, 'enrichment');
        $this->givenProductsWithFamilyByRankAndAxis('webcams', 7, 4, 'consistency');

        $ranksDistribution = $this->query->byFamily(new FamilyCode('mugs'), new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 8,
                        'rank_2' => 0,
                        'rank_3' => 11,
                        'rank_4' => 6,
                        'rank_5' => 0,
                    ]
                ]
            ],
            'consistency' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 0,
                        'rank_2' => 0,
                        'rank_3' => 0,
                        'rank_4' => 7,
                        'rank_5' => 13,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedRanksDistribution, $ranksDistribution->toArray());
    }

    public function test_it_returns_ranks_distribution_by_category()
    {
        $this->givenCategory('winter');
        $this->givenSubCategory('winter', 'winter_clothes');
        $this->givenSubCategory('winter_clothes', 'clothes_accessories');
        $this->givenSubCategory('winter_clothes', 'coats');
        $this->givenSubCategory('clothes_accessories', 'clothes_belts');

        $this->givenCategory('accessories');
        $this->givenSubCategory('accessories', 'belts');

        // There will be 9 products with rank 1 in enrichment for the full category 'winter_clothes'
        $this->givenProductsWithCategoriesByRankAndAxis(['winter_clothes'], 3, 1, 'enrichment');
        $this->givenProductsWithCategoriesByRankAndAxis(['clothes_accessories'], 2, 1, 'enrichment');
        $this->givenProductsWithCategoriesByRankAndAxis(['clothes_belts'], 4, 1, 'enrichment');

        // There will be 12 products with rank 2 in enrichment for the full category 'winter_clothes'
        $this->givenProductsWithCategoriesByRankAndAxis(['winter_clothes', 'clothes_belts'], 3, 2, 'enrichment');
        $this->givenProductsWithCategoriesByRankAndAxis(['coats', 'clothes_accessories'], 7, 2, 'enrichment');
        $this->givenProductsWithCategoriesByRankAndAxis(['coats', 'belts'], 2, 2, 'enrichment');

        // There will be 7 products with rank 1 in consistency for the full category 'winter_clothes'
        $this->givenProductsWithCategoriesByRankAndAxis(['winter_clothes', 'accessories'], 3, 1, 'consistency');
        $this->givenProductsWithCategoriesByRankAndAxis(['winter', 'clothes_belts', 'belts'], 4, 1, 'consistency');

        // other rates for uninvolved categories
        $this->givenProductsWithCategoriesByRankAndAxis(['winter'], 2, 1, 'enrichment');
        $this->givenProductsWithCategoriesByRankAndAxis(['belts'], 2, 1, 'enrichment');
        $this->givenProductsWithCategoriesByRankAndAxis(['winter'], 1, 1, 'consistency');
        $this->givenProductsWithCategoriesByRankAndAxis(['accessories'], 2, 1, 'consistency');

        $ranksDistribution = $this->query->byCategory(new CategoryCode('winter_clothes'), new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 9,
                        'rank_2' => 12,
                        'rank_3' => 0,
                        'rank_4' => 0,
                        'rank_5' => 0,
                    ]
                ]
            ],
            'consistency' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 7,
                        'rank_2' => 0,
                        'rank_3' => 0,
                        'rank_4' => 0,
                        'rank_5' => 0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedRanksDistribution, $ranksDistribution->toArray());
    }

    public function test_it_returns_ranks_distribution_without_data_for_one_axis()
    {
        $this->givenFamily('mugs');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 3, 1, 'enrichment');

        $this->givenFamily('webcams');
        $this->givenProductsWithFamilyByRankAndAxis('webcams', 3, 1, 'enrichment');
        $this->givenProductsWithFamilyByRankAndAxis('webcams', 1, 4, 'consistency');

        $ranksDistribution = $this->query->byFamily(new FamilyCode('mugs'), new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'enrichment' => [
                'ecommerce' => [
                    'en_US' => [
                        'rank_1' => 3,
                        'rank_2' => 0,
                        'rank_3' => 0,
                        'rank_4' => 0,
                        'rank_5' => 0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedRanksDistribution, $ranksDistribution->toArray());
    }

    private function givenProductsByRankAndAxisForWholeCatalog(int $countProducts, int $rank, string $axis): void
    {
        $this->createProductAxesRatesWithDifferentDates($countProducts, $rank, $axis, function () {
            $this->lastProductId++;
            return new ProductId($this->lastProductId);
        });
    }

    private function givenProductsWithFamilyByRankAndAxis(string $family, int $countProducts, int $rank, string $axis)
    {
        $this->createProductAxesRatesWithDifferentDates($countProducts, $rank, $axis, function () use ($family) {
            return $this->createProductWithFamily($family);
        });
    }

    private function givenProductsWithCategoriesByRankAndAxis(array $categories, int $countProducts, int $rank, string $axis)
    {
        $this->createProductAxesRatesWithDifferentDates($countProducts, $rank, $axis, function () use ($categories) {
            return $this->createProductWithCategories($categories);
        });
    }

    private function createProductAxesRatesWithDifferentDates(int $nbProducts, int $rank, string $axis, callable $createProduct)
    {
        $consolidationDate = new \DateTimeImmutable(self::CONSOLIDATION_DATE);

        for ($i = 0; $i < $nbProducts; $i++) {
            $productId = $createProduct();
            /*
             * The evaluation date is decremented to have at least one product evaluated the exact day of the consolidation
             * and the other products evaluated before the consolidation date.
             */
            $evaluatedAt = 0 === $i ? $consolidationDate : $consolidationDate->modify(sprintf('-%d DAY', $i));

            $this->productAxisRateRepository->save([
                // Latest rates compared to the consolidation date
                new ProductAxisRates(
                    new AxisCode($axis),
                    $productId,
                    $evaluatedAt,
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $this->getRateFromRank($rank))
                ),
                // Too old rates compared to the consolidation date
                new ProductAxisRates(
                    new AxisCode($axis),
                    $productId,
                    $evaluatedAt->modify('-1 DAY'),
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $this->getRateFromRank($this->getDifferentRank($rank)))
                ),
                // Too young rates compared to the consolidation date
                new ProductAxisRates(
                    new AxisCode($axis),
                    $productId,
                    (new \DateTimeImmutable(self::CONSOLIDATION_DATE))->modify('+1 DAY'),
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $this->getRateFromRank($this->getDifferentRank($rank)))
                ),
            ]);
        }
    }

    private function givenFamily(string $familyCode): void
    {
        $family = $this
            ->get('akeneo_integration_tests.base.family.builder')
            ->build([
                'code' => $familyCode,
            ]);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function givenCategory(string $categoryCode): void
    {
        $this->createCategory([
            'code' => $categoryCode
        ]);
    }

    private function givenSubCategory(string $parent, string $categoryCode): void
    {
        $this->createCategory([
            'code' => $categoryCode,
            'parent' => $parent,
        ]);
    }

    private function createProductWithFamily(string $family): ProductId
    {
        $this->lastProductId++;
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(sprintf('product_%d', $this->lastProductId))
            ->withFamily($family)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId(intval($product->getId()));
    }

    private function createProductWithCategories(array $categories): ProductId
    {
        $this->lastProductId++;
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(sprintf('product_%d', $this->lastProductId))
            ->withCategories(...$categories)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId(intval($product->getId()));
    }

    private function getDifferentRank(int $rank): int
    {
        $ranks = array_diff([1,2,3,4,5], [$rank]);

        return $ranks[array_rand($ranks)];
    }

    private function getRateFromRank(int $rank): Rate
    {
        return new Rate(100 - $rank*10);
    }
}
