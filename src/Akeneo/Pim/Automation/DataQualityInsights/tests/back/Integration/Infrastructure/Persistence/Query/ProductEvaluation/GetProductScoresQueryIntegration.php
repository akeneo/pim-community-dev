<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
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
    public function test_it_returns_the_latest_scores_by_product_uuids()
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productUuidA = $this->createProduct('product_A')->getUuid()->toString();
        $productUuidB = $this->createProduct('product_B')->getUuid()->toString();
        $productUuidC = $this->createProduct('product_C')->getUuid()->toString();
        $productUuidD = $this->createProduct('product_D')->getUuid()->toString();

        $this->resetProductsScores();

        $productsScores = [
            'product_A_latest_scores' => new ProductScores(
                ProductUuid::fromString($productUuidA),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36))
            ),
            'product_A_previous_scores' => new ProductScores(
                ProductUuid::fromString($productUuidA),
                new \DateTimeImmutable('2020-01-07'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(76))
                    ->addRate($channelMobile, $localeFr, new Rate(67))
            ),
            'product_B_latest_scores' => new ProductScores(
                ProductUuid::fromString($productUuidB),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
            'product_B_previous_scores' => new ProductScores(
                ProductUuid::fromString($productUuidB),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(81))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
            'other_product_scores' => new ProductScores(
                ProductUuid::fromString($productUuidC),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(87))
                    ->addRate($channelMobile, $localeFr, new Rate(95))
            ),
        ];

        $this->get(ProductScoreRepository::class)->saveAll(array_values($productsScores));

        $expectedProductsScores = [
            $productUuidA => $productsScores['product_A_latest_scores']->getScores(),
            $productUuidB => $productsScores['product_B_latest_scores']->getScores(),
        ];

        $productUuidCollection = $this->get(ProductUuidFactory::class)->createCollection([(string)$productUuidA, (string)$productUuidB, (string)$productUuidD]);
        $productAxesRates = $this->get(GetProductScoresQuery::class)->byProductUuidCollection($productUuidCollection);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productAxesRates);
    }
}
