<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductScoresByUuidsQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresByUuidsQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_the_scores_by_product_identifiers()
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productA = $this->createProduct('product_A');
        $productB = $this->createProduct('product_B');
        $productC = $this->createProduct('product_C');
        $productD = $this->createProduct('product_D');

        $this->resetProductsScores();
        $productUuidA = $this->get(ProductUuidFactory::class)->create((string)$productA->getUuid());
        $productUuidB = $this->get(ProductUuidFactory::class)->create((string)$productB->getUuid());
        $productUuidC = $this->get(ProductUuidFactory::class)->create((string)$productC->getUuid());

        $productsScores = [
            'product_A_scores' => new Write\ProductScores(
                $productUuidA,
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(89))
                    ->addRate($channelMobile, $localeFr, new Rate(54)),
            ),
            'product_B_scores' => new Write\ProductScores(
                $productUuidB,
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(34))
                    ->addRate($channelMobile, $localeFr, new Rate(87)),
            ),
            'other_product_scores' => new Write\ProductScores(
                $productUuidC,
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                new ChannelLocaleRateCollection()
            ),
        ];

        $this->get(ProductScoreRepository::class)->saveAll(array_values($productsScores));

        $expectedProductsScores = [
            $productA->getIdentifier() => new Read\Scores(
                $productsScores['product_A_scores']->getScores(),
                $productsScores['product_A_scores']->getScoresPartialCriteria(),
            ),
            $productB->getIdentifier() => new Read\Scores(
                $productsScores['product_B_scores']->getScores(),
                $productsScores['product_B_scores']->getScoresPartialCriteria(),
            ),
        ];

        $productScores = $this->get(GetProductScoresByUuidsQuery::class)->byProductUuids([
            $productA->getUuid(),
            $productB->getUuid(),
            $productD->getUuid(),
        ]);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productScores);
    }
}
