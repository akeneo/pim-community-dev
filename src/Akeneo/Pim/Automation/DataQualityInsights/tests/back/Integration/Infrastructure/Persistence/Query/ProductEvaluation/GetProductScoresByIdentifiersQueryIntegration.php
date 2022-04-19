<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductScoresByIdentifiersQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresByIdentifiersQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_the_latest_scores_by_product_identifiers()
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productA = $this->createProduct('product_A');
        $productB = $this->createProduct('product_B');
        $productC = $this->createProduct('product_C');
        $productD = $this->createProduct('product_D');

        $this->resetProductsScores();
        $productIdA = $this->get(ProductIdFactory::class)->create((string)$productA->getId());
        $productIdB = $this->get(ProductIdFactory::class)->create((string)$productB->getId());
        $productIdC = $this->get(ProductIdFactory::class)->create((string)$productC->getId());

        $productsScores = [
            'product_A_latest_scores' => new Write\ProductScores(
                $productIdA,
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(89))
                    ->addRate($channelMobile, $localeFr, new Rate(54)),
            ),
            'product_A_previous_scores' => new Write\ProductScores(
                $productIdA,
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67)),
                new ChannelLocaleRateCollection()
            ),
            'product_B_latest_scores' => new Write\ProductScores(
                $productIdB,
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(34))
                    ->addRate($channelMobile, $localeFr, new Rate(87)),
            ),
            'product_B_previous_scores' => new Write\ProductScores(
                $productIdB,
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(81))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                new ChannelLocaleRateCollection()
            ),
            'other_product_scores' => new Write\ProductScores(
                $productIdC,
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
                $productsScores['product_A_latest_scores']->getScores(),
                $productsScores['product_A_latest_scores']->getScoresPartialCriteria(),
            ),
            $productB->getIdentifier() => new Read\Scores(
                $productsScores['product_B_latest_scores']->getScores(),
                $productsScores['product_B_latest_scores']->getScoresPartialCriteria(),
            ),
        ];

        $productScores = $this->get(GetProductScoresByIdentifiersQuery::class)->byProductIdentifiers([
            $productA->getIdentifier(),
            $productB->getIdentifier(),
            $productD->getIdentifier(),
        ]);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productScores);
    }
}
