<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductModelIdsFromProductModelCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class GetDataQualityInsightsPropertiesForProductModelProjectionSpec extends ObjectBehavior
{
    public function let(
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        GetProductModelIdsFromProductModelCodesQueryInterface $getProductModelIdsFromProductModelCodesQuery,
    ) {
        $this->beConstructedWith($getProductModelScoresQuery, $getProductModelIdsFromProductModelCodesQuery);
    }

    public function it_returns_additional_properties_from_product_model_codes(
        $getProductModelScoresQuery,
        $getProductModelIdsFromProductModelCodesQuery
    ) {
        $productId42 = new ProductId(42);
        $productId123 = new ProductId(123);
        $productId456 = new ProductId(456);
        $productIds = [
            'product_1' => $productId42,
            'product_2' => $productId123,
            'product_without_rates' => $productId456,
        ];
        $productModelCodes = [
            'product_1', 'product_2', 'product_without_rates'
        ];

        $getProductModelIdsFromProductModelCodesQuery->execute($productModelCodes)->willReturn($productIds);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getProductModelScoresQuery->byProductModelIds(ProductIdCollection::fromProductIds([$productId42, $productId123, $productId456]))->willReturn([
            42 => (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(81))
                ->addRate($channelMobile, $localeFr, new Rate(30))
                ->addRate($channelEcommerce, $localeEn, new Rate(73)),
            123 => (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(66)),
        ]);

        $this->fromProductModelCodes($productModelCodes)->shouldReturn([
            'product_1' => [
                'data_quality_insights' => [
                    'scores' => [
                        'mobile' => [
                            'en_US' => 2,
                            'fr_FR' => 5,
                        ],
                        'ecommerce' => [
                            'en_US' => 3,
                        ],
                    ],
                ],
            ],
            'product_2' => [
                'data_quality_insights' => [
                    'scores' => [
                        'mobile' => [
                            'en_US' => 4,
                        ],
                    ],
                ],
            ],
            'product_without_rates' => [
                'data_quality_insights' => ['scores' => []],
            ],
        ]);
    }
}
