<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
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
        ComputeProductsKeyIndicators                        $computeProductsKeyIndicators,
        ProductEntityIdFactoryInterface                     $idFactory
    ) {
        $this->beConstructedWith($getProductScoresQuery, $computeProductsKeyIndicators, $idFactory);
    }

    public function it_returns_additional_properties_from_product_identifiers(
        GetProductScoresQueryInterface                      $getProductScoresQuery,
        ComputeProductsKeyIndicators                        $computeProductsKeyIndicators,
        ProductEntityIdFactoryInterface                     $idFactory
    ) {
        $uuid42 = Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $uuid123 = Uuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $uuid456 = Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee');
        $uuids = [$uuid42, $uuid123, $uuid456];

        $productUuidCollection = ProductUuidCollection::fromProductUuids([
            ProductUuid::fromString($uuid42->toString()),
            ProductUuid::fromString($uuid123->toString()),
            ProductUuid::fromString($uuid456->toString()),
        ]);

        $idFactory->createCollection(['df470d52-7723-4890-85a0-e79be625e2ed', 'fef37e64-a963-47a9-b087-2cc67968f0a2', '6d125b99-d971-41d9-a264-b020cd486aee'])->willReturn($productUuidCollection);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getProductScoresQuery->byProductUuidCollection($productUuidCollection)->willReturn([
            $uuid42->toString() => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(81))
                    ->addRate($channelMobile, $localeFr, new Rate(30))
                    ->addRate($channelEcommerce, $localeEn, new Rate(73)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(78))
                    ->addRate($channelMobile, $localeFr, new Rate(46))
                    ->addRate($channelEcommerce, $localeEn, new Rate(81))
            ),
            $uuid123->toString() => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(66)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channelMobile, $localeEn, new Rate(74)),
            )
        ]);

        $productsKeyIndicators = [
            $uuid42->toString() => [
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
            $uuid123->toString() => [
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

        $this->fromProductUuids($uuids)->shouldReturn([
            $uuid42->toString() => [
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
                    'key_indicators' => $productsKeyIndicators['df470d52-7723-4890-85a0-e79be625e2ed']
                ],
            ],
            $uuid123->toString() => [
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
                    'key_indicators' => $productsKeyIndicators['fef37e64-a963-47a9-b087-2cc67968f0a2']
                ],
            ],
            $uuid456->toString() => [
                'data_quality_insights' => ['scores' => [], 'scores_partial_criteria' => [], 'key_indicators' => []],
            ],
        ]);
    }
}
