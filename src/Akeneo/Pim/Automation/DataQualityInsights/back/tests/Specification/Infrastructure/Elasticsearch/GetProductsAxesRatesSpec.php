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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

class GetProductsAxesRatesSpec extends ObjectBehavior
{
    public function let(GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $this->beConstructedWith($getLatestProductAxesRatesQuery);
    }

    public function it_gives_product_rates_by_product_ids(GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $productIds = [new ProductId(42), new ProductId(123)];
        $getLatestProductAxesRatesQuery->byProductIds($productIds)->willReturn([
            42 => [
                "enrichment" => [
                    "mobile" => [
                        "en_US" => [
                            'rank' => 2,
                            'rate' => 86
                        ],
                        "fr_FR" => [
                            'rank' => 5,
                            'rate' => 16
                        ],
                    ],
                    "ecommerce" => [
                        "en_US" => [
                            'rank' => 3,
                            'rate' => 72
                        ],
                        "fr_FR" => [
                            'rank' => 5,
                            'rate' => 0
                        ],
                    ],
                ],
                "consistency" => [
                    "mobile" => [
                        "en_US" => [
                            'rank' => 1,
                            'rate' => 96
                        ],
                        "fr_FR" => [
                            'rank' => 1,
                            'rate' => 96
                        ]
                    ],
                    "ecommerce" => [
                        "en_US" => [
                            'rank' => 2,
                            'rate' => 82
                        ],
                        "fr_FR" => [
                            'rank' => 2,
                            'rate' => 81
                        ]
                    ],
                ],
            ],
            123 => []
        ]);

        $this->fromProductIds($productIds)->shouldReturn([
            42 => [
                "enrichment" => [
                    "mobile" => [
                        "en_US" => "B",
                        "fr_FR" => "E"
                    ],
                    "ecommerce" => [
                        "en_US" => "C",
                        "fr_FR" => "E"
                    ],
                ],
                "consistency" => [
                    "mobile" => [
                        "en_US" => "A",
                        "fr_FR" => "A"
                    ],
                    "ecommerce" => [
                        "en_US" => "B",
                        "fr_FR" => "B"
                    ],
                ],
            ],
            123 => []
        ]);
    }
}
