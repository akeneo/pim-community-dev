<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductScoreRepositoryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_save_multiple_products_scores(): void
    {
        $productIdA = $this->createProduct('product_A')->getId();
        $productIdB = $this->createProduct('product_B')->getId();

        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productScoreA1 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-17'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(96))
                ->addRate($channelMobile, $localeFr, new Rate(36))
        );
        $productScoreA2 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
                ->addRate($channelMobile, $localeFr, new Rate(42))
        );
        $productScoreB = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0))
        );
        // To ensure that it doesn't crash when saving a unknown product
        $unknownProductScore = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0))
        );

        $this->resetProductsScores();
        $this->get(ProductScoreRepository::class)->saveAll([$productScoreA1, $productScoreA2, $unknownProductScore, $productScoreB]);

        $this->assertCountProductsScores(3);
        $this->assertProductScoreExists($productScoreA1);
        $this->assertProductScoreExists($productScoreA2);
        $this->assertProductScoreExists($productScoreB);
    }

    public function test_it_purges_scores_older_than_a_given_date(): void
    {
        $productIdA = $this->createProduct('product_A')->getId();
        $productIdB = $this->createProduct('product_B')->getId();

        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productScoreA1 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-18'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(96))
                ->addRate($channelMobile, $localeFr, new Rate(36))
        );
        $productScoreA2 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-17'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(79))
                ->addRate($channelMobile, $localeFr, new Rate(12))
        );
        $productScoreA3 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
                ->addRate($channelMobile, $localeFr, new Rate(42))
        );
        $productScoreB = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0))
        );

        $this->resetProductsScores();
        $this->get(ProductScoreRepository::class)->saveAll([$productScoreA1, $productScoreA2, $productScoreA3, $productScoreB]);
        $this->get(ProductScoreRepository::class)->purgeUntil(new \DateTimeImmutable('2020-11-17'));

        $this->assertCountProductsScores(3);
        $this->assertProductScoreExists($productScoreA1);
        $this->assertProductScoreExists($productScoreA2);
        $this->assertProductScoreExists($productScoreB);
    }

    private function assertCountProductsScores(int $expectedCount): void
    {
        $countProductsScores = $this->get('database_connection')->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_data_quality_insights_product_score;
SQL
        )->fetchColumn();

        $this->assertSame($expectedCount, intval($countProductsScores));
    }

    private function assertProductScoreExists(ProductScores $expectedProductScore): void
    {
        $productScore = $this->get('database_connection')->executeQuery(<<<SQL
SELECT * FROM pim_data_quality_insights_product_score
WHERE product_id = :productId AND evaluated_at = :evaluatedAt;
SQL,
            [
                'productId' => $expectedProductScore->getProductId()->toInt(),
                'evaluatedAt' => $expectedProductScore->getEvaluatedAt()->format('Y-m-d'),
            ]
        )->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($productScore);

        $expectedScore = $expectedProductScore->getScores()->mapWith(function (Rate $score) {
            return [
                'rank' => Rank::fromRate($score)->toInt(),
                'value' => $score->toInt(),
            ];
        });

        $this->assertEquals($expectedScore, json_decode($productScore['scores'], true));
    }
}
