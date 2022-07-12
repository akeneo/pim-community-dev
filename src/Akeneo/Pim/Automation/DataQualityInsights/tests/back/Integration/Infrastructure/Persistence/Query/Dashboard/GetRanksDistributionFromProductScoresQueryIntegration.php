<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard\GetRanksDistributionFromProductScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GetRanksDistributionFromProductScoresQueryIntegration extends TestCase
{
    private const CONSOLIDATION_DATE = '2020-01-15';

    private int $lastInsertedProductIdentifier ;

    private GetRanksDistributionFromProductScoresQuery $query;

    private ProductScoreRepositoryInterface $productScoreRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productScoreRepository = $this->get(ProductScoreRepository::class);
        $this->query = $this->get(GetRanksDistributionFromProductScoresQuery::class);
        $this->lastInsertedProductIdentifier = 0;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_ranks_distribution_for_whole_catalog()
    {
        $this->givenProductsByRankAndAxisForWholeCatalog(3, 1);
        $this->givenProductsByRankAndAxisForWholeCatalog(9, 2);
        $this->givenProductsByRankAndAxisForWholeCatalog(7, 5);

        $ranksDistribution = $this->query->forWholeCatalog(new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'ecommerce' => [
                'en_US' => [
                    'rank_1' => 3,
                    'rank_2' => 9,
                    'rank_3' => 0,
                    'rank_4' => 0,
                    'rank_5' => 7,
                ]
            ],
        ];

        $this->assertEquals($expectedRanksDistribution, $ranksDistribution->toArray());
    }

    public function test_it_returns_ranks_distribution_by_family()
    {
        $this->givenFamily('mugs');
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 8, 1);
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 11, 3);
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 6, 4);
        $this->givenProductsWithFamilyByRankAndAxis('mugs', 13, 5);

        $this->givenFamily('webcams');
        $this->givenProductsWithFamilyByRankAndAxis('webcams', 6, 1);
        $this->givenProductsWithFamilyByRankAndAxis('webcams', 7, 4);

        $ranksDistribution = $this->query->byFamily(new FamilyCode('mugs'), new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'ecommerce' => [
                'en_US' => [
                    'rank_1' => 8,
                    'rank_2' => 0,
                    'rank_3' => 11,
                    'rank_4' => 6,
                    'rank_5' => 13,
                ],
            ],
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

        // There will be 9 products with rank 1 for the full category 'winter_clothes'
        $this->givenProductsWithCategoriesByRankAndAxis(['winter_clothes'], 3, 1);
        $this->givenProductsWithCategoriesByRankAndAxis(['clothes_accessories'], 2, 1);
        $this->givenProductsWithCategoriesByRankAndAxis(['clothes_belts'], 4, 1);

        // There will be 12 products with rank 2 for the full category 'winter_clothes'
        $this->givenProductsWithCategoriesByRankAndAxis(['winter_clothes', 'clothes_belts'], 3, 2);
        $this->givenProductsWithCategoriesByRankAndAxis(['coats', 'clothes_accessories'], 7, 2);
        $this->givenProductsWithCategoriesByRankAndAxis(['coats', 'belts'], 2, 2);

        // other rates for uninvolved categories
        $this->givenProductsWithCategoriesByRankAndAxis(['winter'], 2, 1);
        $this->givenProductsWithCategoriesByRankAndAxis(['belts'], 2, 1);
        $this->givenProductsWithCategoriesByRankAndAxis(['winter'], 1, 1);
        $this->givenProductsWithCategoriesByRankAndAxis(['accessories'], 2, 1);

        $ranksDistribution = $this->query->byCategory(new CategoryCode('winter_clothes'), new \DateTimeImmutable(self::CONSOLIDATION_DATE));

        $expectedRanksDistribution = [
            'ecommerce' => [
                'en_US' => [
                    'rank_1' => 9,
                    'rank_2' => 12,
                    'rank_3' => 0,
                    'rank_4' => 0,
                    'rank_5' => 0,
                ]
            ],
        ];

        $this->assertEquals($expectedRanksDistribution, $ranksDistribution->toArray());
    }

    private function givenProductsByRankAndAxisForWholeCatalog(int $countProducts, int $rank): void
    {
        $this->createProductAxesRatesWithDifferentDates($countProducts, $rank, function () {
            return $this->createProductWithFamily('mugs');
        });
    }

    private function givenProductsWithFamilyByRankAndAxis(string $family, int $countProducts, int $rank)
    {
        $this->createProductAxesRatesWithDifferentDates($countProducts, $rank, function () use ($family) {
            return $this->createProductWithFamily($family);
        });
    }

    private function givenProductsWithCategoriesByRankAndAxis(array $categories, int $countProducts, int $rank)
    {
        $this->createProductAxesRatesWithDifferentDates($countProducts, $rank, function () use ($categories) {
            return $this->createProductWithCategories($categories);
        });
    }

    private function createProductAxesRatesWithDifferentDates(int $nbProducts, int $rank, callable $createProduct)
    {
        $consolidationDate = new \DateTimeImmutable(self::CONSOLIDATION_DATE);

        $productUuids = [];
        for ($i = 0; $i < $nbProducts; $i++) {
            /** @var ProductUuid $productUuid */
            $productUuid = $createProduct();
            $productUuids[] = Uuid::fromString($productUuid->__toString());

            $this->get('database_connection')->executeQuery(
                "DELETE FROM pim_data_quality_insights_product_score WHERE product_uuid = ?",
                [$productUuid->toBytes()]
            );

            $this->productScoreRepository->saveAll([
                new ProductScores(
                    $productUuid,
                    $consolidationDate,
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $this->getRateFromRank($rank)),
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), $this->getRateFromRank($rank)),
                ),
            ]);
        }

        $this->getProductIndexer()->indexFromProductUuids($productUuids);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
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

    private function createProductWithFamily(string $family): ProductUuid
    {
        $this->lastInsertedProductIdentifier++;
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(sprintf('product_%d', $this->lastInsertedProductIdentifier))
            ->withFamily($family)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return ProductUuid::fromUuid($product->getUuid());
    }

    private function createProductWithCategories(array $categories): ProductUuid
    {
        $this->lastInsertedProductIdentifier++;
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(sprintf('product_%d', $this->lastInsertedProductIdentifier))
            ->withCategories(...$categories)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return ProductUuid::fromUuid($product->getUuid());
    }

    private function getRateFromRank(int $rank): Rate
    {
        return new Rate(100 - $rank*10);
    }

    private function getProductIndexer(): ProductIndexer
    {
        return $this->get('pim_catalog.elasticsearch.indexer.product');
    }
}
