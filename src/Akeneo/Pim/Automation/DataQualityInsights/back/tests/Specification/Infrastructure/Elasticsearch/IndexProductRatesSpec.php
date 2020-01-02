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

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IndexProductRatesSpec extends ObjectBehavior
{
    public function let(Client $esClient, GetProductAxesRates $getProductAxesRates)
    {
        $this->beConstructedWith($esClient, $getProductAxesRates);
    }

    public function it_indexes_product_rates(
        Client $esClient,
        GetProductAxesRates $getProductAxesRates
    ) {
        $getProductAxesRates->get(new ProductId(123))->willReturn([
            'consistency' => ['rates' => ['ecommerce' => ['en_US' => 'A']]],
            'enrichment' => ['rates' => ['ecommerce' => ['en_US' => 'B']]],
        ]);
        $getProductAxesRates->get(new ProductId(456))->willReturn([
            'consistency' => ['rates' => ['ecommerce' => ['en_US' => 'C']]],
            'enrichment' => ['rates' => ['ecommerce' => ['en_US' => 'D']]],
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
        GetProductAxesRates $getProductAxesRates
    ) {
        $getProductAxesRates->get(new ProductId(123))->willReturn([
            'consistency' => ['rates' => ['ecommerce' => ['en_US' => 'A']]],
        ]);
        $getProductAxesRates->get(new ProductId(456))->willReturn([
            'consistency' => ['rates' => []],
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
