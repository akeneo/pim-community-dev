<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductModelsIndex
{
    public function __construct(
        private Client                              $esClient,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
    ) {
    }

    public function execute(ProductIdCollection $productIdCollection): void
    {
        $productModelsScores = $this->getProductModelScoresQuery->byProductModelIds($productIdCollection);

        foreach ($productIdCollection->toArray() as $productModelId) {
            $productModelId = $productModelId->toInt();
            if (!array_key_exists($productModelId, $productModelsScores)) {
                continue;
            }
            $productModelScores = $productModelsScores[$productModelId];

            $this->updateProductIndex($productModelId, $productModelScores);
        }
    }

    private function updateProductIndex(int $productModelId, ChannelLocaleRateCollection $productModelScores): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'inline' => "ctx._source.data_quality_insights = params;",
                    'params' => [
                        'scores' => $productModelScores->toArrayIntRank(),
                    ],
                ],
                'query' => [
                    'term' => [
                        'id' => sprintf('product_model_%d', $productModelId),
                    ],
                ],
            ]
        );
    }
}
