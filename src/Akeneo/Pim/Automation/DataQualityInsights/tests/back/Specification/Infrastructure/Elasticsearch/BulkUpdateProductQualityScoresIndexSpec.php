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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
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
            ->during('__invoke', [(ProductIdCollection::fromInts([123, 456, 42]))]);
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
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 4]],
                            'key_indicators' => $productsKeyIndicators[123]
                        ],
                    ]
                ],
                'product_456' => [
                    'script' => [
                        'inline' => "ctx._source.data_quality_insights = params;",
                        'params' => [
                            'scores' => ['ecommerce' => ['en_US' => 1]],
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 3]],
                            'key_indicators' => $productsKeyIndicators[456]
                        ],
                    ]
                ]
            ]
        )
            ->shouldBeCalled();

        $this->__invoke(ProductIdCollection::fromInts([123, 456, 42]));
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
                            'scores_partial_criteria' => ['ecommerce' => ['en_US' => 4]],
                            'key_indicators' => $productModelsKeyIndicators[123]
                        ],
                    ]
                ],
                'product_model_456' => [
                    'script' => [
                        'inline' => "ctx._source.data_quality_insights = params;",
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

        $this->__invoke(ProductIdCollection::fromInts([123, 456, 42]));
    }

    private function getData(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');

        $productIdCollection = ProductIdCollection::fromProductIds([new ProductId(123), new ProductId(456), new ProductId(42)]);
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
             'productIdCollection' => $productIdCollection,
             'scores' => $scores,
             'keyIndicators' => $keyIndicators
         ];
    }
}
