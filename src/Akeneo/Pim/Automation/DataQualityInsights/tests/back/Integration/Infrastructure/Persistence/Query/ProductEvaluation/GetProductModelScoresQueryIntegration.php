<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductModelScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_product_model_scores_by_id(): void
    {
        $productModelIds = $this->provideProductModelIds();
        $scores = $this->provideScores($productModelIds);

        $this->assertEqualsScore($scores, $productModelIds['idA']);

        $this->assertEqualsNoScore();
    }

    public function test_it_returns_product_model_scores_by_ids(): void
    {
        $productModelIds = $this->provideProductModelIds();
        $scores = $this->provideScores($productModelIds);

        $expectedProductModelsScores = [
            $productModelIds['idA'] => new Read\Scores(
                $scores['product_model_A_scores']->getScores(),
                $scores['product_model_A_scores']->getScoresPartialCriteria(),
            ),
            $productModelIds['idB'] => new Read\Scores(
                $scores['product_model_B_scores']->getScores(),
                $scores['product_model_B_scores']->getScoresPartialCriteria(),
            ),
        ];

        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([
            $productModelIds['idA'],
            $productModelIds['idB']
        ]);
        $productAxesRates = $this->get(GetProductModelScoresQuery::class)
            ->byProductModelIdCollection($productModelIdCollection);

        $this->assertEqualsCanonicalizing($expectedProductModelsScores, $productAxesRates);
    }

    /**
     * @param Write\ProductScores[] $scores
     */
    private function assertEqualsScore(array $scores, int $productModelId)
    {
        $searchProductId = $this->get(ProductModelIdFactory::class)->create((string)$productModelId);
        $result = $this->get(GetProductModelScoresQuery::class)->byProductModelId($searchProductId);
        $expectedScores = new Read\Scores(
            $scores['product_model_A_scores']->getScores(),
            $scores['product_model_A_scores']->getScoresPartialCriteria()
        );

        $this->assertEquals($expectedScores, $result);
    }

    private function assertEqualsNoScore()
    {
        $missingProductId = $this->get(ProductModelIdFactory::class)->create('1590');
        $result = $this->get(GetProductModelScoresQuery::class)->byProductModelId($missingProductId);
        $this->assertEquals($result, new Read\Scores(
            new ChannelLocaleRateCollection(),
            new ChannelLocaleRateCollection()
        ));
    }

    /**
     * @return int[]
     */
    private function provideProductModelIds(): array
    {
        $this->createMinimalFamilyAndFamilyVariant('family_V', 'family_V_1');
        $productModelIdA = $this->createProductModel('product_model_A', 'family_V_1')->getId();
        $productModelIdB = $this->createProductModel('product_model_B', 'family_V_1')->getId();
        $productModelIdC = $this->createProductModel('product_model_C', 'family_V_1')->getId();

        return ['idA' => $productModelIdA, 'idB' => $productModelIdB, 'idC' => $productModelIdC];
    }

    /**
     * @param int[]
     * @return Write\ProductScores[]
     */
    private function provideScores(array $productModelIds): array
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $this->resetProductModelsScores();

        $productModelsScores = [
            'product_model_A_scores' => new Write\ProductScores(
                $this->get(ProductModelIdFactory::class)->create((string)$productModelIds['idA']),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(86))
                    ->addRate($channelMobile, $localeFr, new Rate(46)),
            ),
            'product_model_B_scores' => new Write\ProductScores(
                $this->get(ProductModelIdFactory::class)->create((string)$productModelIds['idB']),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(97))
                    ->addRate($channelMobile, $localeFr, new Rate(98)),
            ),
            'other_product_model_scores' => new Write\ProductScores(
                $this->get(ProductModelIdFactory::class)->create((string)$productModelIds['idC']),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(78))
                    ->addRate($channelMobile, $localeFr, new Rate(46)),
            )
        ];

        $this->get(ProductModelScoreRepository::class)->saveAll(array_values($productModelsScores));

        return $productModelsScores;
    }
}
