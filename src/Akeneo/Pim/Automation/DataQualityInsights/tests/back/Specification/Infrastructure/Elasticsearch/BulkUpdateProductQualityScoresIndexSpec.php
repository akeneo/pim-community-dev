<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class BulkUpdateProductQualityScoresIndexSpec extends ObjectBehavior
{
    public function it_does_not_update_when_type_is_incorrect(
        Client $esClient,
        GetProductScoresQueryInterface $getProductScoresQuery,
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        ComputeProductsKeyIndicators $computeProductsKeyIndicators,
    ) {
        $this->beConstructedWith(
            $esClient,
            $getProductScoresQuery,
            $getProductModelScoresQuery,
            $computeProductsKeyIndicators,
            'a_type'
        );

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [(ProductUuidCollection::fromInts([123, 456, 42]))]);
    }

    public function it_updates_products_index(
        Client $esClient,
        GetProductScoresQueryInterface $getProductScoresQuery,
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        ComputeProductsKeyIndicators $computeProductsKeyIndicators,
    ) {
        $this->beConstructedWith(
            $esClient,
            $getProductScoresQuery,
            $getProductModelScoresQuery,
            $computeProductsKeyIndicators,
            ProductInterface::class
        );

        $productIds = $this->getData()['productIdCollection'];

        $getProductScoresQuery->byProductIds($productIds)->willReturn($this->getData()['scores']);

        $productsKeyIndicators = $this->getData()['keyIndicators'];

        $computeProductsKeyIndicators->compute($productIds)->willReturn($productsKeyIndicators);

        $esClient->bulkUpdate(
            ['product_123', 'product_456', 'product_42'],
            [
                'product_123' => [
                    'script' => [
                        'inline' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 5]],
                            'key_indicators' => $productsKeyIndicators[123]
                        ],
                    ]
                ],
                'product_456' => [
                    'script' => [
                        'inline' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 1]],
                            'key_indicators' => $productsKeyIndicators[456]
                        ],
                    ]
                ]
            ]
        )
            ->shouldBeCalled();

        $this->__invoke(ProductUuidCollection::fromInts([123, 456, 42]));
    }

    public function it_updates_product_models_index(
        Client $esClient,
        GetProductScoresQueryInterface $getProductScoresQuery,
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        ComputeProductsKeyIndicators $computeProductsKeyIndicators,
    ) {
        $this->beConstructedWith(
            $esClient,
            $getProductScoresQuery,
            $getProductModelScoresQuery,
            $computeProductsKeyIndicators,
            ProductModelInterface::class
        );

        $productModelIds = $this->getData()['productIdCollection'];

        $getProductModelScoresQuery->byProductModelIds($productModelIds)->willReturn($this->getData()['scores']);

        $productModelsKeyIndicators = $this->getData()['keyIndicators'];

        $computeProductsKeyIndicators->compute($productModelIds)->willReturn($productModelsKeyIndicators);

        $esClient->bulkUpdate(
            ['product_model_123', 'product_model_456', 'product_model_42'],
            [
                'product_model_123' => [
                    'script' => [
                        'inline' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 5]],
                            'key_indicators' => $productModelsKeyIndicators[123]
                        ],
                    ]
                ],
                'product_model_456' => [
                    'script' => [
                        'inline' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 1]],
                            'key_indicators' => $productModelsKeyIndicators[456]
                        ],
                    ]
                ]
            ]
        )
            ->shouldBeCalled();

        $this->__invoke(ProductUuidCollection::fromInts([123, 456, 42]));
    }

    private function getData(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        $productUuidCollection = ProductUuidCollection::fromProductUuids([
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')),
        ]);
        $scores = [
            'df470d52-7723-4890-85a0-e79be625e2ed' => (new ChannelLocaleRateCollection)
                ->addRate($channel, $locale, new Rate(10)),
            'fef37e64-a963-47a9-b087-2cc67968f0a2' => (new ChannelLocaleRateCollection)
                ->addRate($channel, $locale, new Rate(96)),
        ];
        $keyIndicators = [
            'df470d52-7723-4890-85a0-e79be625e2ed' => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => true,
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
                ],
            ],
            '6d125b99-d971-41d9-a264-b020cd486aee' => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => null,
                        'has_image' => null,
                    ],
                ],
            ],
        ];

         return [
             'productIdCollection' => $productUuidCollection,
             'scores' => $scores,
             'keyIndicators' => $keyIndicators
         ];
    }
}
