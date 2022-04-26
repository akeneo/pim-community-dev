<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductUuidsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class GetDataQualityInsightsPropertiesForProductProjectionSpec extends ObjectBehavior
{
    public function let(
        GetProductScoresQueryInterface                      $getProductScoresQuery,
        GetProductUuidsFromProductIdentifiersQueryInterface $getProductUuidsFromProductIdentifiersQuery,
        ComputeProductsKeyIndicators                        $computeProductsKeyIndicators,
        ProductEntityIdFactoryInterface                     $idFactory
    ) {
        $this->beConstructedWith($getProductScoresQuery, $getProductUuidsFromProductIdentifiersQuery, $computeProductsKeyIndicators, $idFactory);
    }

    public function it_returns_additional_properties_from_product_identifiers(
        GetProductScoresQueryInterface                      $getProductScoresQuery,
        GetProductUuidsFromProductIdentifiersQueryInterface $getProductUuidsFromProductIdentifiersQuery,
        ComputeProductsKeyIndicators                        $computeProductsKeyIndicators,
        ProductEntityIdFactoryInterface                     $idFactory
    ) {
        $productUuid42 = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $productUuid123 = ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productUuid456 = ProductUuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee');
        $productIds = [
            'product_1' => $productUuid42,
            'product_2' => $productUuid123,
            'product_without_rates' => $productUuid456,
        ];
        $productIdentifiers = [
            'product_1', 'product_2', 'product_without_rates'
        ];
        $productUuidCollection = ProductUuidCollection::fromProductUuids([$productUuid42, $productUuid123, $productUuid456]);

        $getProductUuidsFromProductIdentifiersQuery->execute($productIdentifiers)->willReturn($productIds);
        $idFactory->createCollection(['df470d52-7723-4890-85a0-e79be625e2ed', 'fef37e64-a963-47a9-b087-2cc67968f0a2', '6d125b99-d971-41d9-a264-b020cd486aee'])->willReturn($productUuidCollection);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getProductScoresQuery->byProductUuidCollection($productUuidCollection)->willReturn([
            'df470d52-7723-4890-85a0-e79be625e2ed' => (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(81))
                ->addRate($channelMobile, $localeFr, new Rate(30))
                ->addRate($channelEcommerce, $localeEn, new Rate(73)),
            'fef37e64-a963-47a9-b087-2cc67968f0a2' => (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(66)),
        ]);

        $productsKeyIndicators = [
            'df470d52-7723-4890-85a0-e79be625e2ed' => [
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
            'fef37e64-a963-47a9-b087-2cc67968f0a2' => [
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

        $computeProductsKeyIndicators->compute($productUuidCollection)->willReturn($productsKeyIndicators);

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
                    'key_indicators' => $productsKeyIndicators['df470d52-7723-4890-85a0-e79be625e2ed']
                ],
            ],
            'product_2' => [
                'data_quality_insights' => [
                    'scores' => [
                        'mobile' => [
                            'en_US' => 4,
                        ],
                    ],
                    'key_indicators' => $productsKeyIndicators['fef37e64-a963-47a9-b087-2cc67968f0a2']
                ],
            ],
            'product_without_rates' => [
                'data_quality_insights' => ['scores' => [], 'key_indicators' => []],
            ],
        ]);
    }
}
