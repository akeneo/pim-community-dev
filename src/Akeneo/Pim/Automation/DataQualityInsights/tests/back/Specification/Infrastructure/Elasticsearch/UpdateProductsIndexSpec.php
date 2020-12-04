<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Domain\Model\Axis\Consistency;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class UpdateProductsIndexSpec extends ObjectBehavior
{
    public function let(
        Client $esClient,
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        ComputeProductsKeyIndicators $computeProductsKeyIndicators
    ) {
        $this->beConstructedWith($esClient, $getLatestProductAxesRanksQuery, $computeProductsKeyIndicators);
    }

    public function it_updates_products_index(
        $esClient,
        $getLatestProductAxesRanksQuery,
        $computeProductsKeyIndicators
    ) {
        $consistency = new Consistency();
        $enrichment = new Enrichment();
        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $productIds = [new ProductId(123), new ProductId(456), new ProductId(42)];

        $getLatestProductAxesRanksQuery->byProductIds($productIds)->willReturn([
            123 => (new AxisRankCollection())
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(1))
                )
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(2))
                ),
            456 => (new AxisRankCollection())
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(3))
                )
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(5))
                ),
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
                'inline' => "ctx._source.rates = params.rates; ctx._source.data_quality_insights = params.data_quality_insights;",
                'params' => [
                    'rates' => [
                        'consistency' => ['ecommerce' => ['en_US' => 1]],
                        'enrichment' => ['ecommerce' => ['en_US' => 2]],
                    ],
                    'data_quality_insights' => ['key_indicators' => $productsKeyIndicators[123]],
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
                'inline' => "ctx._source.rates = params.rates; ctx._source.data_quality_insights = params.data_quality_insights;",
                'params' => [
                    'rates' => [
                        'consistency' => ['ecommerce' => ['en_US' => 3]],
                        'enrichment' => ['ecommerce' => ['en_US' => 5]],
                    ],
                    'data_quality_insights' => ['key_indicators' => $productsKeyIndicators[456]],
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
