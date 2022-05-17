<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
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
    public function test_it_returns_the_scores_by_product_uuids()
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
            'product_A_scores' => new Write\ProductScores(
                ProductUuid::fromString($productUuidA),
                new \DateTimeImmutable('2020-01-08'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(96))
                    ->addRate($channelMobile, $localeFr, new Rate(36)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(89))
                    ->addRate($channelMobile, $localeFr, new Rate(23))
            ),
            'product_B_scores' => new Write\ProductScores(
                ProductUuid::fromString($productUuidB),
                new \DateTimeImmutable('2020-01-09'),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(100))
                    ->addRate($channelMobile, $localeFr, new Rate(95)),
                (new ChannelLocaleRateCollection())
                    ->addRate($channelMobile, $localeEn, new Rate(89))
                    ->addRate($channelMobile, $localeFr, new Rate(98)),
            ),
            'other_product_scores' => new Write\ProductScores(
                ProductUuid::fromString($productUuidC),
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
            $productUuidA => new Read\Scores(
                $productsScores['product_A_scores']->getScores(),
                $productsScores['product_A_scores']->getScoresPartialCriteria()
            ),
            $productUuidB => new Read\Scores(
                $productsScores['product_B_scores']->getScores(),
                $productsScores['product_B_scores']->getScoresPartialCriteria()
            ),
        ];

        $productUuidCollection = $this->get(ProductUuidFactory::class)->createCollection([$productUuidA, $productUuidB, $productUuidD]);
        $productAxesRates = $this->get(GetProductScoresQuery::class)->byProductUuidCollection($productUuidCollection);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productAxesRates);
    }
}
