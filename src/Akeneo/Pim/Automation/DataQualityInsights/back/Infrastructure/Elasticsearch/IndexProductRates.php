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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

class IndexProductRates
{
    /** @var Client */
    private $esClient;

    /** @var GetLatestProductAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    public function __construct(Client $esClient, GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $this->esClient = $esClient;
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
    }

    public function execute(array $productIds): void
    {
        $productIds = array_map(function ($productId) {
            return new ProductId($productId);
        }, $productIds);

        $productsAxesRates = $this->getLatestProductAxesRatesQuery->byProductIds($productIds);

        foreach ($productsAxesRates as $productId => $productAxesRates) {
            $this->indexProductRates($productId, $productAxesRates->getRanks());
        }
    }

    private function indexProductRates(int $productId, array $productAxesRates): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.rates = params",
                    'params' => $productAxesRates,
                ],
                'query' => [
                    'term' => [
                        'id' => sprintf('product_%d', $productId),
                    ],
                ],
            ]
        );
    }
}
