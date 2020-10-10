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

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductsKeyIndicators;
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

    /** @var GetProductsKeyIndicators */
    private $getProductsKeyIndicators;

    public function __construct(
        Client $esClient,
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        GetProductsKeyIndicators $getProductsKeyIndicators
    ) {
        $this->esClient = $esClient;
        $this->getLatestProductAxesRanksQuery = $getLatestProductAxesRanksQuery;
        $this->getProductsKeyIndicators = $getProductsKeyIndicators;
    }

    public function execute(array $productIds): void
    {
        $productsAxesRanks = $this->getLatestProductAxesRanksQuery->byProductIds(
            array_map(function ($productId) {
                return new ProductId($productId);
            }, $productIds)
        );
        $productsKeyIndicators = $this->getProductsKeyIndicators->get($productIds);

        foreach ($productIds as $productId) {
            $axesRanks = array_key_exists($productId, $productsAxesRanks) ? $productsAxesRanks[$productId] : [];
            $keyIndicators = array_key_exists($productId, $productsKeyIndicators) ? $productsKeyIndicators[$productId] : [];
            $this->indexProductRanks($productId, $axesRanks, $keyIndicators);
        }
    }

    private function indexProductRanks(int $productId, ?AxisRankCollection $productAxesRanks, ?array $keyIndicators): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'inline' => "ctx._source.rates = params.rates; ctx._source.data_quality_insights = params.data_quality_insights;",
                    'params' => [
                        'rates' => ($productAxesRanks ? $productAxesRanks->toArrayInt() : []),
                        'data_quality_insights' => ['key_indicators' => ($keyIndicators ? $keyIndicators : [])],
                    ],
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
