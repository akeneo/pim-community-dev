<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class UpdateProductModelsIndexSpec extends ObjectBehavior
{
    public function let(
        Client                               $esClient,
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        ComputeProductsKeyIndicators         $computeProductsKeyIndicators
    )
    {
        $this->beConstructedWith($esClient, $getProductModelScoresQuery, $computeProductsKeyIndicators);
    }

    public function it_updates_product_model_models_index(
        $esClient,
        $getProductModelScoresQuery,
        $computeProductsKeyIndicators
    )
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $productModelIds = ProductIdCollection::fromProductIds([new ProductId(123), new ProductId(456), new ProductId(42)]);

        $getProductModelScoresQuery->byProductModelIds($productModelIds)->willReturn([
            123 => (new ChannelLocaleRateCollection)
                ->addRate($channelEcommerce, $localeEn, new Rate(10)),
            456 => (new ChannelLocaleRateCollection)
                ->addRate($channelEcommerce, $localeEn, new Rate(96)),
        ]);

        $productModelsKeyIndicators = [
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

        $this->execute(ProductIdCollection::fromInts([123, 456, 42]));
    }
}
