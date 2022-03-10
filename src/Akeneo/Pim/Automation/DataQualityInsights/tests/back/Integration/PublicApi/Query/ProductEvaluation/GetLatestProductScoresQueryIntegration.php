<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetLatestProductScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetLatestProductScoresQueryIntegration extends DataQualityInsightsTestCase
{
    /**
     * https://github.com/akeneo/pim-community-dev/pull/15486#discussion_r732547768
     * FYI, the good practice would be to emit the event that DQI listen to create a DQI product score. This way, the coupling is done on event (which is a contract).
     * By creating a whole product, it makes DQI completely coupled by the tests to the enrichment context, and on a big part of the application.
     * This test can be broken by a change in enrichment, even if it does not break DQI itself.
     */
    public function test_it_returns_the_latest_scores_by_product_identifiers()
    {
        [$productA, $productB, $productC, $productD] = $this->loadProductScores();

        $expectedProductsScoreCollections = [
            $productA->getIdentifier() => new ProductScoreCollection([
                'mobile' => [
                    'en_US' => new ProductScore('A', 96),
                    'fr_FR' => new ProductScore('E', 36),
                ],
            ]),
            $productB->getIdentifier() => new ProductScoreCollection([
                'mobile' => [
                    'en_US' => new ProductScore('A', 100),
                    'fr_FR' => new ProductScore('A', 95),
                ],
            ]),
        ];

        $productScoreCollections = $this->get('akeneo.pim.automation.data_quality_insights.public_api.get_latest_product_score_query')->byProductIdentifiers([
            $productA->getIdentifier(),
            $productB->getIdentifier(),
            $productD->getIdentifier(),
        ]);

        $this->assertEqualsCanonicalizing($expectedProductsScoreCollections, $productScoreCollections);
    }

    public function test_it_returns_the_latest_scores_by_product_identifier()
    {
        [$productA] = $this->loadProductScores();

        $expectedProductsScoreCollection = new ProductScoreCollection([
                'mobile' => [
                    'en_US' => new ProductScore('A', 96),
                    'fr_FR' => new ProductScore('E', 36),
                ],
        ]);

        $productScoreCollection = $this->get(GetLatestProductScoresQuery::class)->byProductIdentifier($productA->getIdentifier());

        $this->assertEqualsCanonicalizing($expectedProductsScoreCollection, $productScoreCollection);
    }

    private function loadProductScores() {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productA = $this->createProduct('product_A');
        $productB = $this->createProduct('product_B');
        $productC = $this->createProduct('product_C');
        $productD = $this->createProduct('product_D');

        $this->resetProductsScores();

        $productsScores = [
            'product_A_latest_scores' => new ProductScores(
                new ProductId($productA->getId()),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36))
            ),
            'product_A_previous_scores' => new ProductScores(
                new ProductId($productA->getId()),
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67))
            ),
            'product_B_latest_scores' => new ProductScores(
                new ProductId($productB->getId()),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
            'product_B_previous_scores' => new ProductScores(
                new ProductId($productB->getId()),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(81))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
            'other_product_scores' => new ProductScores(
                new ProductId($productC->getId()),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
        ];

        $this->get(ProductScoreRepository::class)->saveAll(array_values($productsScores));

        return [$productA, $productB, $productC, $productD];
    }
}
