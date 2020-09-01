<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

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

final class GetRatesForProductProjectionSpec extends ObjectBehavior
{
    public function let(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery
    ) {
        $this->beConstructedWith($getLatestProductAxesRanksQuery, $getProductIdsFromProductIdentifiersQuery);
    }

    public function it_returns_product_rates_from_product_identifiers(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery
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
            ],
            'product_2' => [
                'rates' => [
                    'enrichment' => [
                        'mobile' => [
                            'en_US' => 4,
                        ],
                    ],
                ],
            ],
            'product_without_rates' => [
                'rates' => [],
            ],
        ]);
    }
}
