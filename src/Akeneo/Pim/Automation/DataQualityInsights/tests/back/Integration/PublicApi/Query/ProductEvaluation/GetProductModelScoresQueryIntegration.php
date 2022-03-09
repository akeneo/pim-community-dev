<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductModelScoreRepository;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductModelScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductModelScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductModelScoresQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_the_scores_by_product_model_codes(): void
    {
        [$productModelA, $productModelB, $productModelC, $productModelD] = $this->loadProductModelScores();

        $expectedProductModelsScoreCollections = [
            $productModelA->getCode() => new ProductModelScoreCollection([
                'mobile' => [
                    'en_US' => new ProductModelScore('A', 95),
                    'fr_FR' => new ProductModelScore('A', 100),
                ],
            ]),
            $productModelB->getCode() => new ProductModelScoreCollection([
                'mobile' => [
                    'en_US' => new ProductModelScore('D', 67),
                    'fr_FR' => new ProductModelScore('C', 76),
                ],
            ]),
        ];

        $productModelScoreCollections = $this->get(GetProductModelScoresQuery::class)->byProductModelCodes([
            $productModelA->getCode(),
            $productModelB->getCode(),
            $productModelD->getCode(),
        ]);

        $this->assertEqualsCanonicalizing($expectedProductModelsScoreCollections, $productModelScoreCollections);
    }

    public function test_it_returns_the_scores_by_product_model_code(): void
    {
        [$productModelA] = $this->loadProductModelScores();

        $expectedProductModelsScoreCollection = new ProductModelScoreCollection([
                'mobile' => [
                    'en_US' => new ProductModelScore('C', 76),
                    'fr_FR' => new ProductModelScore('D', 67),
                ],
        ]);

        $productModelScoreCollection = $this->get(GetProductModelScoresQuery::class)->byProductModelCode($productModelA->getCode());

        $this->assertEqualsCanonicalizing($expectedProductModelsScoreCollection, $productModelScoreCollection);
    }

    private function loadProductModelScores(): array {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $this->createMinimalFamilyAndFamilyVariant('family_V', 'family_V_1');
        $productModelA = $this->createProductModel('product_model_A', 'family_V_1');
        $productModelB = $this->createProductModel('product_model_B', 'family_V_1');
        $productModelC = $this->createProductModel('product_model_C', 'family_V_1');
        $productModelD = $this->createProductModel('product_model_D','family_V_1');

        $this->resetProductModelsScores();

        $productModelsScores = [
            'product_model_A_scores' => new ProductScores(
                new ProductId($productModelA->getId()),
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67))
            ),
            'product_model_B_scores' => new ProductScores(
                new ProductId($productModelB->getId()),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),

            'other_product_model_scores' => new ProductScores(
                new ProductId($productModelC->getId()),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
        ];

        $this->get(ProductModelScoreRepository::class)->saveAll(array_values($productModelsScores));

        return [$productModelA, $productModelB, $productModelC, $productModelD];
    }
}
