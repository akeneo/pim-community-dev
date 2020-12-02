<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetLatestProductScoresQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\ProductScoreRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetLatestProductScoresByIdentifiersQueryIntegration extends DataQualityInsightsTestCase
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

        $expectedProductsScores = [
            $productA->getIdentifier() => $productsScores['product_A_latest_scores']->getScores(),
            $productB->getIdentifier() => $productsScores['product_B_latest_scores']->getScores(),
        ];

        $productScores = $this->get(GetLatestProductScoresByIdentifiersQuery::class)->byProductIdentifiers([
            $productA->getIdentifier(),
            $productB->getIdentifier(),
            $productD->getIdentifier(),
        ]);

        var_dump($productScores);

        $this->assertEqualsCanonicalizing($expectedProductsScores, $productScores);
    }
}
