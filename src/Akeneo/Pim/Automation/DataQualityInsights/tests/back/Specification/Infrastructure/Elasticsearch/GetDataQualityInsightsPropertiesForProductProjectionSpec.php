<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class GetDataQualityInsightsPropertiesForProductProjectionSpec extends ObjectBehavior
{
    public function let(
        GetProductScoresQueryInterface                    $getProductScoresQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery,
        ComputeProductsKeyIndicators                      $computeProductsKeyIndicators,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $this->beConstructedWith($getProductScoresQuery, $getProductIdsFromProductIdentifiersQuery, $computeProductsKeyIndicators, $idFactory);
    }

    public function it_returns_additional_properties_from_product_identifiers(
        $getProductScoresQuery,
        $getProductIdsFromProductIdentifiersQuery,
        $computeProductsKeyIndicators,
        $idFactory
    ) {
        $productId42 = new ProductId(42);
        $productId123 = new ProductId(123);
        $productId456 = new ProductId(456);
        $productIds = [
            'product_1' => $productId42,
            'product_2' => $productId123,
            'product_without_rates' => $productId456,
        ];
        $productIdentifiers = [
            'product_1', 'product_2', 'product_without_rates'
        ];
        $collection = ProductIdCollection::fromProductIds([$productId42, $productId123, $productId456]);

        $getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers)->willReturn($productIds);
        $idFactory->createCollection(['42', '123', '456'])->willReturn($collection);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getProductScoresQuery->byProductIds($collection)->willReturn([
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

        $productsKeyIndicators = [
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

        $computeProductsKeyIndicators->compute($collection)->willReturn($productsKeyIndicators);

        $this->fromProductIdentifiers($productIdentifiers)->shouldReturn([
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
                    'scores_partial_criteria' => [
                        'mobile' => [
                            'en_US' => 3,
                            'fr_FR' => 5,
                        ],
                        'ecommerce' => [
                            'en_US' => 2,
                        ],
                    ],
                    'key_indicators' => $productsKeyIndicators[42]
                ],
            ],
            'product_2' => [
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
                    'key_indicators' => $productsKeyIndicators[123]
                ],
            ],
            'product_without_rates' => [
                'data_quality_insights' => ['scores' => [], 'scores_partial_criteria' => [], 'key_indicators' => []],
            ],
        ]);
    }
}
