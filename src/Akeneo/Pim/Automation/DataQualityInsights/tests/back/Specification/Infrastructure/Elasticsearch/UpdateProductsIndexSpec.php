<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class UpdateProductsIndexSpec extends ObjectBehavior
{
    public function let(
        Client $esClient,
        GetLatestProductScoresQueryInterface $getProductScoresQuery,
        ComputeProductsKeyIndicators $computeProductsKeyIndicators
    ) {
        $this->beConstructedWith($esClient, $getProductScoresQuery, $computeProductsKeyIndicators);
    }

    public function it_updates_products_index(
        $esClient,
        $getProductScoresQuery,
        $computeProductsKeyIndicators
    ) {
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $productIds = [new ProductId(123), new ProductId(456), new ProductId(42)];

        $getProductScoresQuery->byProductIds($productIds)->willReturn([
            123 => (new ChannelLocaleRateCollection)
                ->addRate($channelEcommerce, $localeEn, new Rate(10)),
            456 => (new ChannelLocaleRateCollection)
                ->addRate($channelEcommerce, $localeEn, new Rate(96)),
        ]);

        $productsKeyIndicators = [
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

        $computeProductsKeyIndicators->compute($productIds)->willReturn($productsKeyIndicators);

        $esClient->updateByQuery([
            'script' => [
                'inline' => "ctx._source.data_quality_insights = params;",
                'params' => [
                    'scores' => ['ecommerce' => ['en_US' => 5]],
                    'key_indicators' => $productsKeyIndicators[123]
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_123',
                ],
            ],
        ])->shouldBeCalled();
        $esClient->updateByQuery([
            'script' => [
                'inline' => "ctx._source.data_quality_insights = params;",
                'params' => [
                    'scores' => ['ecommerce' => ['en_US' => 1]],
                    'key_indicators' => $productsKeyIndicators[456]
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_456',
                ],
            ],
        ])->shouldBeCalled();

        $this->execute([123, 456, 42]);
    }
}
