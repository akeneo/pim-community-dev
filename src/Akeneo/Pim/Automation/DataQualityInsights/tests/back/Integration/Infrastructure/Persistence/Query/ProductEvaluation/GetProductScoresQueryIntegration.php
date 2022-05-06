<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
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
    public function test_it_returns_the_scores_by_product_ids()
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
            'product_A_scores' => new ProductScores(
                new ProductId($productIdA),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36))
            ),
            'product_B_scores' => new ProductScores(
                new ProductId($productIdB),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
            'other_product_scores' => new ProductScores(
                new ProductId($productIdC),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
        ];

        $this->get(ProductScoreRepository::class)->saveAll(array_values($productsScores));

        $expectedProductsScores = [
            $productIdA => $productsScores['product_A_scores']->getScores(),
            $productIdB => $productsScores['product_B_scores']->getScores(),
        ];

        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([(string)$productIdA, (string)$productIdB, (string)$productIdD]);
        $productAxesRates = $this->get(GetProductScoresQuery::class)->byProductIds($productModelIdCollection);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productAxesRates);
    }
}
