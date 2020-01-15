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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class GetRatesForProductProjectionSpec extends ObjectBehavior
{
    public function let(
        GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery
    ) {
        $this->beConstructedWith($getLatestProductAxesRatesQuery, $getProductIdsFromProductIdentifiersQuery);
    }

    public function it_returns_product_rates_from_product_identifiers(
        GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
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

        $getLatestProductAxesRatesQuery->byProductIds($productIds)->willReturn([
            42 => new ProductAxesRates(new ProductId(42), [
                'enrichment' => [
                        'mobile' => [
                            'en_US' => ['value' => 83, 'rank' => 2],
                            'fr_FR' => ['value' => 9, 'rank' => 5],
                        ],
                        'ecommerce' => [
                            'en_US' => ['value' => 78, 'rank' => 3],
                        ],
                    ],
                    'consistency' => [
                        'mobile' => [
                            'en_US' => ['value' => 98, 'rank' => 1],
                        ],
                    ],
            ]),
            123 => new ProductAxesRates(new ProductId(123), [
                'enrichment' => [
                        'mobile' => [
                            'en_US' => ['value' => 67, 'rank' => 4],
                        ],
                    ],
            ])
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
        ]);
    }
}
