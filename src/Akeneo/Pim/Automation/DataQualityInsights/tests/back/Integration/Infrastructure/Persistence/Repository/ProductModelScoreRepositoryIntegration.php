<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductModelScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelScoreRepositoryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_save_multiple_products_scores(): void
    {
        $this->createMinimalFamilyAndFamilyVariant('family_V', 'family_V_1');
        $productModelIdA = $this->createProductModel('product_model_A', 'family_V_1')->getId();
        $productModelIdB = $this->createProductModel('product_model_B', 'family_V_1')->getId();

        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productModelScoreA = new ProductScores(
            new ProductModelId($productModelIdA),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
                ->addRate($channelMobile, $localeFr, new Rate(42)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
        );
        $productModelScoreB = new ProductScores(
            new ProductModelId($productModelIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
        );

        // Test that scores of new product models are well inserted
        $this->resetProductModelsScores();
        $this->get(ProductModelScoreRepository::class)->saveAll([$productModelScoreA, $productModelScoreB]);

        $this->assertCountProductModelsScores(2);
        $this->assertProductModelScoreExists($productModelScoreA);
        $this->assertProductModelScoreExists($productModelScoreB);

        $updatedProductModelScoreA = new ProductScores(
            new ProductModelId($productModelIdA),
            new \DateTimeImmutable('2020-11-17'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(96))
                ->addRate($channelMobile, $localeFr, new Rate(36)),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(96))
        );

        // Test that scores of existing product models are well updated
        $this->get(ProductModelScoreRepository::class)->saveAll([$updatedProductModelScoreA, $productModelScoreB]);
        $this->assertCountProductModelsScores(2);
        $this->assertProductModelScoreExists($updatedProductModelScoreA);
        $this->assertProductModelScoreExists($productModelScoreB);
    }

    private function assertCountProductModelsScores(int $expectedCount): void
    {
        $countProductModelsScores = $this->get('database_connection')->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_data_quality_insights_product_model_score;
SQL
        )->fetchOne();

        $this->assertSame($expectedCount, intval($countProductModelsScores));
    }

    private function assertProductModelScoreExists(ProductScores $expectedProductModelScore): void
    {
        $productModelScore = $this->get('database_connection')->executeQuery(<<<SQL
SELECT * FROM pim_data_quality_insights_product_model_score
WHERE product_model_id = :productModelId AND evaluated_at = :evaluatedAt;
SQL,
            [
                'productModelId' => $expectedProductModelScore->getEntityId()->toInt(),
                'evaluatedAt' => $expectedProductModelScore->getEvaluatedAt()->format('Y-m-d'),
            ]
        )->fetchAssociative();

        $this->assertNotEmpty($productModelScore);

        $expectedScores = $this->formatScoresForComparison($expectedProductModelScore->getScores());
        $this->assertEquals($expectedScores, \json_decode($productModelScore['scores'], true));

        $expectedScoresPartialCriteria = $this->formatScoresForComparison($expectedProductModelScore->getScoresPartialCriteria());
        $this->assertEquals($expectedScoresPartialCriteria, \json_decode($productModelScore['scores_partial_criteria'], true));
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
}
