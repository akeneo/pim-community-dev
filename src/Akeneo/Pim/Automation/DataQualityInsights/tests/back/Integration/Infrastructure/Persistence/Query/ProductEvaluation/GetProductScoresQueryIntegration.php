<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScoresQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_returns_the_latest_scores_by_product_ids()
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productIdA = $this->createProduct('product_A')->getId();
        $productIdB = $this->createProduct('product_B')->getId();
        $productIdC = $this->createProduct('product_C')->getId();
        $productIdD = $this->createProduct('product_D')->getId();

        $this->resetProductsScores();

        $productsScores = [
            'product_A_latest_scores' => new Write\ProductScores(
                new ProductId($productIdA),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(89))
                    ->addRate($channelMobile, $localeFr, new Rate(23))
            ),
            'product_A_previous_scores' => new Write\ProductScores(
                new ProductId($productIdA),
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(48)),
            ),
            'product_B_latest_scores' => new Write\ProductScores(
                new ProductId($productIdB),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(89))
                    ->addRate($channelMobile, $localeFr, new Rate(98)),
            ),
            'product_B_previous_scores' => new Write\ProductScores(
                new ProductId($productIdB),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(81))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(85))
                    ->addRate($channelMobile, $localeFr, new Rate(97)),
            ),
            'other_product_scores' => new Write\ProductScores(
                new ProductId($productIdC),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(67))
                    ->addRate($channelMobile, $localeFr, new Rate(81)),
            ),
        ];

        $this->get(ProductScoreRepository::class)->saveAll(array_values($productsScores));

        $expectedProductsScores = [
            $productIdA => new Read\Scores(
                $productsScores['product_A_latest_scores']->getScores(),
                $productsScores['product_A_latest_scores']->getScoresPartialCriteria()
            ),
            $productIdB => new Read\Scores(
                $productsScores['product_B_latest_scores']->getScores(),
                $productsScores['product_B_latest_scores']->getScoresPartialCriteria()
            ),
        ];

        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([(string)$productIdA, (string)$productIdB, (string)$productIdD]);
        $productAxesRates = $this->get(GetProductScoresQuery::class)->byProductIds($productModelIdCollection);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productAxesRates);
    }
}
