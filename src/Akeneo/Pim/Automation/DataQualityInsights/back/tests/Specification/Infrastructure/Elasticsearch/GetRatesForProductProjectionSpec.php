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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetProductsAxesRates;
use PhpSpec\ObjectBehavior;

final class GetRatesForProductProjectionSpec extends ObjectBehavior
{
    public function let(
        GetProductsAxesRates $getProductsAxesRates,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery
    ) {
        $this->beConstructedWith($getProductsAxesRates, $getProductIdsFromProductIdentifiersQuery);
    }

    public function it_returns_product_rates_from_product_identifiers(
        GetProductsAxesRates $getProductsAxesRates,
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

        $getProductsAxesRates->fromProductIds($productIds)->willReturn([
            42 => [
                'enrichment' => [
                        'mobile' => [
                            'en_US' => 'B',
                            'fr_FR' => 'E',
                        ],
                        'ecommerce' => [
                            'en_US' => 'C',
                        ],
                    ],
                    'consistency' => [
                        'mobile' => [
                            'en_US' => 'A',
                        ],
                    ],
            ],
            123 => [
                'enrichment' => [
                        'mobile' => [
                            'en_US' => 'B',
                        ],
                    ],
            ]
        ]);

        $this->fromProductIdentifiers($productIdentifiers)->shouldReturn([
            'product_1' => [
                'rates' => [
                    'enrichment' => [
                        'mobile' => [
                            'en_US' => 'B',
                            'fr_FR' => 'E',
                        ],
                        'ecommerce' => [
                            'en_US' => 'C',
                        ],
                    ],
                    'consistency' => [
                        'mobile' => [
                            'en_US' => 'A',
                        ],
                    ],
                ],
            ],
            'product_2' => [
                'rates' => [
                    'enrichment' => [
                        'mobile' => [
                            'en_US' => 'B',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
