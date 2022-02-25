<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
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
    )
    {
        $this->beConstructedWith($esClient, $getProductModelScoresQuery);
    }

    public function it_updates_product_models_index(
        $esClient,
        $getProductModelScoresQuery,
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

        $esClient->updateByQuery([
            'script' => [
                'inline' => "ctx._source.data_quality_insights = params;",
                'params' => [
                    'scores' => ['ecommerce' => ['en_US' => 5]],
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_model_123',
                ],
            ],
        ])->shouldBeCalled();
        $esClient->updateByQuery([
            'script' => [
                'inline' => "ctx._source.data_quality_insights = params;",
                'params' => [
                    'scores' => ['ecommerce' => ['en_US' => 1]],
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_model_456',
                ],
            ],
        ])->shouldBeCalled();

        $this->execute(ProductIdCollection::fromInts([123, 456, 42]));
    }
}
