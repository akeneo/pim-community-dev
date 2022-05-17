<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelScoresByCodesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductModelScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresByCodesQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_the_scores_by_product_model_codes(): void
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $this->createMinimalFamilyAndFamilyVariant('family_V', 'family_V_1');
        $productModelA = $this->createProductModel('product_model_A', 'family_V_1');
        $productModelB = $this->createProductModel('product_model_B', 'family_V_1');
        $productModelC = $this->createProductModel('product_model_C', 'family_V_1');
        $productModelD = $this->createProductModel('product_model_D','family_V_1');

        $productModelIdA = $this->get(ProductModelIdFactory::class)->create((string)$productModelA->getId());
        $productModelIdB = $this->get(ProductModelIdFactory::class)->create((string)$productModelB->getId());
        $productModelIdC = $this->get(ProductModelIdFactory::class)->create((string)$productModelC->getId());

        $this->resetProductModelsScores();

        $productModelsScores = [
            'product_model_A_scores' => new Write\ProductScores(
                $productModelIdA,
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(57))
                    ->addRate($channelMobile, $localeFr, new Rate(83)),
            ),
            'product_model_B_scores' => new Write\ProductScores(
                $productModelIdB,
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(98))
                    ->addRate($channelMobile, $localeFr, new Rate(93)),
            ),

            'other_product_model_scores' => new Write\ProductScores(
                $productModelIdC,
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                new ChannelLocaleRateCollection()
            ),
        ];

        $this->get(ProductModelScoreRepository::class)->saveAll(array_values($productModelsScores));

        $expectedProductModelsScores = [
            $productModelA->getCode() => new Read\Scores(
                $productModelsScores['product_model_A_scores']->getScores(),
                $productModelsScores['product_model_A_scores']->getScoresPartialCriteria(),
            ),
            $productModelB->getCode() => new Read\Scores(
                $productModelsScores['product_model_B_scores']->getScores(),
                $productModelsScores['product_model_B_scores']->getScoresPartialCriteria(),
            ),
        ];

        $productModelsScores = $this->get(GetProductModelScoresByCodesQuery::class)->byProductModelCodes([
            $productModelA->getCode(),
            $productModelB->getCode(),
            $productModelD->getCode(),
        ]);

        $this->assertEqualsCanonicalizing($expectedProductModelsScores, $productModelsScores);
    }
}
