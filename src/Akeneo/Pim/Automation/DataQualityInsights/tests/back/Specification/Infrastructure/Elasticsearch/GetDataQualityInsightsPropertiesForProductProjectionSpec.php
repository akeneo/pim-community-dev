<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Domain\Model\Axis\Consistency;
use PhpSpec\ObjectBehavior;

final class GetDataQualityInsightsPropertiesForProductProjectionSpec extends ObjectBehavior
{
    public function let(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery,
        ComputeProductsKeyIndicators $computeProductsKeyIndicators
    ) {
        $this->beConstructedWith($getLatestProductAxesRanksQuery, $getProductIdsFromProductIdentifiersQuery, $computeProductsKeyIndicators);
    }

    public function it_returns_additional_properties_from_product_identifiers(
        $getLatestProductAxesRanksQuery,
        $getProductIdsFromProductIdentifiersQuery,
        $computeProductsKeyIndicators
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

        $getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers)->willReturn($productIds);

        $consistency = new Consistency();
        $enrichment = new Enrichment();
        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $getLatestProductAxesRanksQuery->byProductIds($productIds)->willReturn([
            42 => (new AxisRankCollection())
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelMobile, $localeEn, Rank::fromInt(2))
                    ->addRank($channelMobile, $localeFr, Rank::fromInt(5))
                    ->addRank($channelEcommerce, $localeEn, Rank::fromInt(3))
                )
                ->add($consistency->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelMobile, $localeEn, Rank::fromInt(1))
                ),
            123 => (new AxisRankCollection())
                ->add($enrichment->getCode(), (new ChannelLocaleRankCollection())
                    ->addRank($channelMobile, $localeEn, Rank::fromInt(4))
                ),
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

        $computeProductsKeyIndicators->compute($productIds)->willReturn($productsKeyIndicators);

        $this->fromProductIdentifiers($productIdentifiers)->shouldReturn([
            'product_1' => [
                'rates' => [
                    'enrichment' => [
                        'mobile' => [
                            'en_US' => 2,
                            'fr_FR' => 5,
                        ],
                        'ecommerce' => [
                            'en_US' => 3,
                        ],
                    ],
                    'consistency' => [
                        'mobile' => [
                            'en_US' => 1,
                        ],
                    ],
                ],
                'data_quality_insights' => ['key_indicators' => $productsKeyIndicators[42]],
            ],
            'product_2' => [
                'rates' => [
                    'enrichment' => [
                        'mobile' => [
                            'en_US' => 4,
                        ],
                    ],
                ],
                'data_quality_insights' => ['key_indicators' => $productsKeyIndicators[123]],
            ],
            'product_without_rates' => [
                'rates' => [],
                'data_quality_insights' => ['key_indicators' => []],
            ],
        ]);
    }
}
