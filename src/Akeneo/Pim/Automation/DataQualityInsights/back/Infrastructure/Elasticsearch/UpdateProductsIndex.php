<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductsIndex
{
    private Client $esClient;

    private GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery;

    private ComputeProductsKeyIndicators $getProductsKeyIndicators;

    public function __construct(
        Client $esClient,
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        ComputeProductsKeyIndicators $getProductsKeyIndicators
    ) {
        $this->esClient = $esClient;
        $this->getLatestProductAxesRanksQuery = $getLatestProductAxesRanksQuery;
        $this->getProductsKeyIndicators = $getProductsKeyIndicators;
    }

    public function execute(array $productIds): void
    {
        $productIds = array_map(fn (int $productId) => new ProductId($productId), $productIds);

        $productsAxesRanks = $this->getLatestProductAxesRanksQuery->byProductIds($productIds);
        $productsKeyIndicators = $this->getProductsKeyIndicators->compute($productIds);

        foreach ($productIds as $productId) {
            $productId = $productId->toInt();
            if (!array_key_exists($productId, $productsAxesRanks)) {
                continue;
            }
            $axesRanks = $productsAxesRanks[$productId];
            $keyIndicators = $productsKeyIndicators[$productId] ?? [];

            $this->updateProductIndex($productId, $axesRanks, $keyIndicators);
        }
    }

    private function updateProductIndex(int $productId, ?AxisRankCollection $productAxesRanks, ?array $keyIndicators): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'inline' => "ctx._source.rates = params.rates; ctx._source.data_quality_insights = params.data_quality_insights;",
                    'params' => [
                        'rates' => ($productAxesRanks ? $productAxesRanks->toArrayInt() : []),
                        'data_quality_insights' => ['key_indicators' => $keyIndicators ?? []],
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
