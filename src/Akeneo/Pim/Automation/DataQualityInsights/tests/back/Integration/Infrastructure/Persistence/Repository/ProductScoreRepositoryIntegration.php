<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Repository;

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
                ->addRate($channelMobile, $localeFr, new Rate(36)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(96))
        );
        $productScoreA2 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
                ->addRate($channelMobile, $localeFr, new Rate(42)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
        );
        $productScoreB = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
        );
        // To ensure that it doesn't crash when saving a unknown product
        $unknownProductScore = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
        );

        $this->resetProductsScores();
        $this->get(ProductScoreRepository::class)->saveAll([$productScoreA1, $productScoreA2, $unknownProductScore, $productScoreB]);

        $this->assertCountProductsScores(2);
        $this->assertProductScoreExists($productScoreA1);
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
                ->addRate($channelMobile, $localeFr, new Rate(36)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(87))
        );
        $productScoreA2 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-17'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(79))
                ->addRate($channelMobile, $localeFr, new Rate(12)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(56))
        );
        $productScoreA3 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
                ->addRate($channelMobile, $localeFr, new Rate(42)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(34))
        );
        $productScoreB = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(85))
        );

        $this->resetProductsScores();
        $this->insertProductScore($productScoreA1);
        $this->insertProductScore($productScoreA2);
        $this->insertProductScore($productScoreA3);
        $this->insertProductScore($productScoreB);
        $this->get(ProductScoreRepository::class)->purgeUntil(new \DateTimeImmutable('2020-11-18'));

        $this->assertCountProductsScores(2);
        $this->assertProductScoreExists($productScoreA1);
        $this->assertProductScoreExists($productScoreB);
    }

    private function assertCountProductsScores(int $expectedCount): void
    {
        $countProductsScores = $this->get('database_connection')->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_data_quality_insights_product_score;
SQL
        )->fetchOne();

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
        )->fetchAssociative();

        $this->assertNotEmpty($productScore);

        $expectedScores = $this->formatScoresForComparison($expectedProductScore->getScores());
        $this->assertEquals($expectedScores, json_decode($productScore['scores'], true));

        $expectedScoresPartialCriteria = $this->formatScoresForComparison($expectedProductScore->getScoresPartialCriteria());
        $this->assertEquals($expectedScoresPartialCriteria, json_decode($productScore['scores_partial_criteria'], true));
    }

    private function formatScoresForComparison(ChannelLocaleRateCollection $scores): array
    {
        return $scores->mapWith(function (Rate $score) {
            return [
                'rank' => Rank::fromRate($score)->toInt(),
                'value' => $score->toInt(),
            ];
        });
    }

    private function insertProductScore(ProductScores $productScore): void
    {
        $insertQuery = <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores, scores_partial_criteria)
VALUES (:productId, :evaluatedAt, :scores, :scoresPartialCriteria);
SQL;

        $this->get('database_connection')->executeQuery($insertQuery, [
            'productId' => $productScore->getProductId()->toInt(),
            'evaluatedAt' => $productScore->getEvaluatedAt()->format('Y-m-d'),
            'scores' => \json_encode($productScore->getScores()->toNormalizedRates()),
            'scoresPartialCriteria' => \json_encode($productScore->getScoresPartialCriteria()->toNormalizedRates()),
        ], [
            'productId' => \PDO::PARAM_INT,
        ]);
    }
}
