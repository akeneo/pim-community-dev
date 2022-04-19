<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
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
        ComputeProductsKeyIndicators $computeProductsKeyIndicators,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $this->beConstructedWith($getProductModelScoresQuery, $getProductModelIdsFromProductModelCodesQuery, $computeProductsKeyIndicators, $idFactory);
    }

    public function it_returns_additional_properties_from_product_model_codes(
        $getProductModelScoresQuery,
        $getProductModelIdsFromProductModelCodesQuery,
        $computeProductsKeyIndicators,
        $idFactory
    ) {
        $productId42 = new ProductId(42);
        $productId123 = new ProductId(123);
        $productId456 = new ProductId(456);
        $productIds = [
            'product_model_1' => $productId42,
            'product_model_2' => $productId123,
            'product_model_without_rates' => $productId456,
        ];
        $productModelCodes = [
            'product_model_1', 'product_model_2', 'product_model_without_rates'
        ];
        $collection = ProductIdCollection::fromProductIds([$productId42, $productId123, $productId456]);

        $getProductModelIdsFromProductModelCodesQuery->execute($productModelCodes)->willReturn($productIds);
        $idFactory->createCollection(['42', '123', '456'])->willReturn($collection);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getProductModelScoresQuery->byProductModelIds($collection)->willReturn([
            42 => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(81))
                    ->addRate($channelMobile, $localeFr, new Rate(30))
                    ->addRate($channelEcommerce, $localeEn, new Rate(73)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(78))
                    ->addRate($channelMobile, $localeFr, new Rate(46))
                    ->addRate($channelEcommerce, $localeEn, new Rate(81))
            ),
            123 => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(66)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(74)),
            )
        ]);

        $productModelsKeyIndicators = [
            42 => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => true,
                        'has_image' => true,
                    ],
                    'fr_FR' => [
                        'good_enrichment' => false,
                        'has_image' => null,
                    ],
                ],
                'mobile' => [
                    'en_US' => [
                        'good_enrichment' => null,
                        'has_image' => false,
                    ],
                ],
            ],
            123 => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => true,
                        'has_image' => true,
                    ],
                    'fr_FR' => [
                        'good_enrichment' => false,
                        'has_image' => true,
                    ],
                ],
                'mobile' => [
                    'en_US' => [
                        'good_enrichment' => false,
                        'has_image' => true,
                    ],
                ],
            ],
        ];

        $computeProductsKeyIndicators->compute(ProductIdCollection::fromProductIds($productIds))->willReturn($productModelsKeyIndicators);

        $this->fromProductModelCodes($productModelCodes)->shouldReturn([
            'product_model_1' => [
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
                    'scores_partial_criteria' => [
                        'mobile' => [
                            'en_US' => 3,
                            'fr_FR' => 5,
                        ],
                        'ecommerce' => [
                            'en_US' => 2,
                        ],
                    ],
                    'key_indicators' => $productModelsKeyIndicators[42]
                ],
            ],
            'product_model_2' => [
                'data_quality_insights' => [
                    'scores' => [
                        'mobile' => [
                            'en_US' => 4,
                        ],
                    ],
                    'scores_partial_criteria' => [
                        'mobile' => [
                            'en_US' => 3,
                        ],
                    ],
                    'key_indicators' => $productModelsKeyIndicators[123]
                ],
            ],
            'product_model_without_rates' => [
                'data_quality_insights' => ['scores' => [], 'scores_partial_criteria' => [], 'key_indicators' => []],
            ],
        ]);
    }
}
