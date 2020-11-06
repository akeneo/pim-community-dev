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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

class IndexProductRates
{
    /** @var Client */
    private $esClient;

    /** @var GetLatestProductAxesRanksQueryInterface */
    private $getLatestProductAxesRanksQuery;

    public function __construct(Client $esClient, GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery)
    {
        $this->esClient = $esClient;
        $this->getLatestProductAxesRanksQuery = $getLatestProductAxesRanksQuery;
    }

    public function execute(array $productIds): void
    {
        $productIds = array_map(fn($productId) => new ProductId($productId), $productIds);

        $productsAxesRanks = $this->getLatestProductAxesRanksQuery->byProductIds($productIds);

        foreach ($productsAxesRanks as $productId => $productAxesRanks) {
            $this->indexProductRanks($productId, $productAxesRanks);
        }
    }

    private function indexProductRanks(int $productId, AxisRankCollection $productAxesRanks): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.rates = params",
                    'params' => $productAxesRanks->toArrayInt(),
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
