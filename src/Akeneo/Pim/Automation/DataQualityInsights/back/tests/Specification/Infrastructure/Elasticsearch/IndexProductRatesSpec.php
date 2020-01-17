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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class IndexProductRatesSpec extends ObjectBehavior
{
    public function let(Client $esClient, GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $this->beConstructedWith($esClient, $getLatestProductAxesRatesQuery);
    }

    public function it_indexes_product_rates(
        Client $esClient,
        GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery
    ) {
        $getLatestProductAxesRatesQuery->byProductIds([new ProductId(123), new ProductId(456), new ProductId(42)])->willReturn([
            123 => new ProductAxesRates(new ProductId(123), [
                'consistency' => ['ecommerce' => ['en_US' => ['value' => 98, 'rank' => 1]]],
                'enrichment' => ['ecommerce' => ['en_US' => ['value' => 86, 'rank' => 2]]],
            ]),
            456 => new ProductAxesRates(new ProductId(456), [
                'consistency' => ['ecommerce' => ['en_US' => ['value' => 78, 'rank' => 3]]],
                'enrichment' => ['ecommerce' => ['en_US' => ['value' => 11, 'rank' => 5]]],
            ])
        ]);

        $esClient->updateByQuery([
            'script' => [
                'source' => "ctx._source.rates = params",
                'params' => [
                    'consistency' => ['ecommerce' => ['en_US' => 1]],
                    'enrichment' => ['ecommerce' => ['en_US' => 2]],
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
                    'consistency' => ['ecommerce' => ['en_US' => 3]],
                    'enrichment' => ['ecommerce' => ['en_US' => 5]],
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
