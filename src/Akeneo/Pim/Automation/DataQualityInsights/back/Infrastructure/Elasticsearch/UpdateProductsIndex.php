<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductsIndex
{
    private Client $esClient;

    private ComputeProductsKeyIndicators $getProductsKeyIndicators;

    private GetLatestProductScoresQueryInterface $getProductScoresQuery;

    public function __construct(
        Client $esClient,
        GetLatestProductScoresQueryInterface $getProductScoresQuery,
        ComputeProductsKeyIndicators $getProductsKeyIndicators
    ) {
        $this->esClient = $esClient;
        $this->getProductsKeyIndicators = $getProductsKeyIndicators;
        $this->getProductScoresQuery = $getProductScoresQuery;
    }

    public function execute(array $productIds): void
    {
        $productIds = array_map(fn (int $productId) => new ProductId($productId), $productIds);

        $productsScores = $this->getProductScoresQuery->byProductIds($productIds);
        $productsKeyIndicators = $this->getProductsKeyIndicators->compute($productIds);

        foreach ($productIds as $productId) {
            $productId = $productId->toInt();
            if (!array_key_exists($productId, $productsScores)) {
                continue;
            }
            $productScores = $productsScores[$productId];
            $keyIndicators = $productsKeyIndicators[$productId] ?? [];

            $this->updateProductIndex($productId, $productScores, $keyIndicators);
        }
    }

    private function updateProductIndex(int $productId, ChannelLocaleRateCollection $productScores, array $keyIndicators): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'inline' => "ctx._source.data_quality_insights = params;",
                    'params' => [
                        'scores' => $productScores->toArrayIntRank() ,
                        'key_indicators' => $keyIndicators
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
