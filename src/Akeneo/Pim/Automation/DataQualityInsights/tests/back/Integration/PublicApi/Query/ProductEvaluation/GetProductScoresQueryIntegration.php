<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class getProductScoresQueryIntegration extends DataQualityInsightsTestCase
{
    /**
     * https://github.com/akeneo/pim-community-dev/pull/15486#discussion_r732547768
     * FYI, the good practice would be to emit the event that DQI listen to create a DQI product score. This way, the coupling is done on event (which is a contract).
     * By creating a whole product, it makes DQI completely coupled by the tests to the enrichment context, and on a big part of the application.
     * This test can be broken by a change in enrichment, even if it does not break DQI itself.
     */
    public function test_it_returns_the_quality_scores_by_product_identifiers()
    {
        [$productA, $productB, $productC, $productD] = $this->loadProductScores();

        $expectedQualityScoreCollections = [
            $productA->getIdentifier() => new QualityScoreCollection([
                'mobile' => [
                    'en_US' => new QualityScore('A', 96),
                    'fr_FR' => new QualityScore('E', 36),
                ],
            ]),
            $productB->getIdentifier() => new QualityScoreCollection([
                'mobile' => [
                    'en_US' => new QualityScore('A', 100),
                    'fr_FR' => new QualityScore('A', 95),
                ],
            ]),
        ];

        $qualityScoreCollections = $this->get(GetProductScoresQuery::class)->byProductUuids([
            $productA->getUuid(),
            $productB->getUuid(),
            $productD->getUuid(),
        ]);

        $this->assertEqualsCanonicalizing($expectedQualityScoreCollections, $qualityScoreCollections);
    }

    public function test_it_returns_the_quality_score_by_product_identifier()
    {
        [$productA] = $this->loadProductScores();

        $expectedQualityScoreCollection = new QualityScoreCollection([
                'mobile' => [
                    'en_US' => new QualityScore('A', 96),
                    'fr_FR' => new QualityScore('E', 36),
                ],
        ]);

        $qualityScoreCollection = $this->get(GetProductScoresQuery::class)->byProductUuid($productA->getUuid());

        $this->assertEqualsCanonicalizing($expectedQualityScoreCollection, $qualityScoreCollection);
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
            'product_A_scores' => new ProductScores(
                ProductUuid::fromUuid($productA->getUuid()),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(86))
                    ->addRate($channelMobile, $localeFr, new Rate(56)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36)),
            ),
            'product_B_scores' => new ProductScores(
                ProductUuid::fromUuid($productB->getUuid()),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(75)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
            ),
            'other_product_scores' => new ProductScores(
                ProductUuid::fromUuid($productC->getUuid()),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(67))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
            ),
        ];

        $this->get(ProductScoreRepository::class)->saveAll(array_values($productsScores));

        return [$productA, $productB, $productC, $productD];
    }
}
