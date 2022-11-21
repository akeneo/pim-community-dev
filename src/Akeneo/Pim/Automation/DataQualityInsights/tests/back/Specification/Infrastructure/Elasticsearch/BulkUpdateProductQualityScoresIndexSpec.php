<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

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
            ->during('__invoke', [$this->getProductsData()['productUuidCollection']]);
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

        $productUuids = $this->getProductsData()['productUuidCollection'];

        $getProductScoresQuery->byProductUuidCollection($productUuids)->willReturn($this->getProductsData()['scores']);

        $productsKeyIndicators = $this->getProductsData()['keyIndicators'];

        $computeProductsKeyIndicators->compute($productUuids)->willReturn($productsKeyIndicators);

        $esClient->bulkUpdate(
            [
                'product_df470d52-7723-4890-85a0-e79be625e2ed',
                'product_fef37e64-a963-47a9-b087-2cc67968f0a2',
                'product_6d125b99-d971-41d9-a264-b020cd486aee'
            ], [
                'product_df470d52-7723-4890-85a0-e79be625e2ed' => [
                    'script' => [
                        'source' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 5]],
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 4]],
                            'key_indicators' => $productsKeyIndicators['df470d52-7723-4890-85a0-e79be625e2ed']
                        ],
                    ]
                ],
                'product_fef37e64-a963-47a9-b087-2cc67968f0a2' => [
                    'script' => [
                        'source' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 1]],
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 3]],
                            'key_indicators' => $productsKeyIndicators['fef37e64-a963-47a9-b087-2cc67968f0a2']
                        ],
                    ]
                ]
            ]
        )
            ->shouldBeCalled();

        $this->__invoke($productUuids);
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

        $productModelIds = $this->getProductModelData()['productModelIdCollection'];

        $getProductModelScoresQuery->byProductModelIdCollection($productModelIds)->willReturn($this->getProductModelData()['scores']);

        $productModelsKeyIndicators = $this->getProductModelData()['keyIndicators'];

        $computeProductsKeyIndicators->compute($productModelIds)->willReturn($productModelsKeyIndicators);

        $esClient->bulkUpdate(
            ['product_model_123', 'product_model_456', 'product_model_42'],
            [
                'product_model_123' => [
                    'script' => [
                        'source' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 5]],
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 4]],
                            'key_indicators' => $productModelsKeyIndicators[123]
                        ],
                    ]
                ],
                'product_model_456' => [
                    'script' => [
                        'source' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 1]],
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 3]],
                            'key_indicators' => $productModelsKeyIndicators[456]
                        ],
                    ]
                ]
            ]
        )
            ->shouldBeCalled();

        $this->__invoke($productModelIds);
    }

    private function getProductsData(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        $productUuidCollection = ProductUuidCollection::fromProductUuids([
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
        ]);
        $scores = [
            'df470d52-7723-4890-85a0-e79be625e2ed' => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(10)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(65)),
            ),
            'fef37e64-a963-47a9-b087-2cc67968f0a2' => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(96)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(78)),
            ),
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
            'productUuidCollection' => $productUuidCollection,
            'scores' => $scores,
            'keyIndicators' => $keyIndicators
        ];
    }

    private function getProductModelData(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        $productModelIdCollection = ProductModelIdCollection::fromStrings(['123', '456', '42']);
        $scores = [
            123 => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(10)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(65)),
            ),
            456 => new Read\Scores(
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(96)),
                (new ChannelLocaleRateCollection)
                    ->addRate($channel, $locale, new Rate(78)),
            ),
        ];
        $keyIndicators = [
            123 => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => true,
                        'has_image' => false,
                    ],
                ],
            ],
            456 => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => true,
                        'has_image' => true,
                    ],
                ],
            ],
            42 => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => null,
                        'has_image' => null,
                    ],
                ],
            ],
        ];

        return [
            'productModelIdCollection' => $productModelIdCollection,
            'scores' => $scores,
            'keyIndicators' => $keyIndicators
        ];
    }
}
