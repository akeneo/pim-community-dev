<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetProductsAxesRates;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class IndexProductRatesSpec extends ObjectBehavior
{
    public function let(Client $esClient, GetProductsAxesRates $getProductsAxesRates)
    {
        $this->beConstructedWith($esClient, $getProductsAxesRates);
    }

    public function it_indexes_product_rates(
        Client $esClient,
        GetProductsAxesRates $getProductsAxesRates
    ) {
        $getProductsAxesRates->fromProductIds([new ProductId(123), new ProductId(456)])->willReturn([
            123 => [
                'consistency' => ['ecommerce' => ['en_US' => 'A']],
                'enrichment' => ['ecommerce' => ['en_US' => 'B']],
            ],
            456 => [
                'consistency' => ['ecommerce' => ['en_US' => 'C']],
                'enrichment' => ['ecommerce' => ['en_US' => 'D']],
            ]
        ]);

        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.rates = params",
                'params' => [
                    'consistency' => ['ecommerce' => ['en_US' => 'A']],
                    'enrichment' => ['ecommerce' => ['en_US' => 'B']],
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
                'source' => "ctx._source.rates = params",
                'params' => [
                    'consistency' => ['ecommerce' => ['en_US' => 'C']],
                    'enrichment' => ['ecommerce' => ['en_US' => 'D']],
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_456',
                ],
            ],
        ])->shouldBeCalled();

        $this->execute([123, 456]);
    }

    public function it_does_not_index_empty_product_rates(
        Client $esClient,
        GetProductsAxesRates $getProductsAxesRates
    ) {
        $getProductsAxesRates->fromProductIds([new ProductId(123), new ProductId(456)])->willReturn([
            123 => [
                'consistency' => ['ecommerce' => ['en_US' => 'A']],
            ],
            456 => []
        ]);
        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.rates = params",
                'params' => [
                    'consistency' => ['ecommerce' => ['en_US' => 'A']],
                ],
            ],
            'query' => [
                'term' => [
                    'id' => 'product_123',
                ],
            ],
        ])->shouldBeCalled();

        $this->execute([123, 456]);
    }
}
