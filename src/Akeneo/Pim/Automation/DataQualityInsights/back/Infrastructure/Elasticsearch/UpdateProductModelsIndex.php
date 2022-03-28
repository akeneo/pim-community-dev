<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
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
    private const PRODUCT_MODEL_IDENTIFIER_PREFIX = 'product_model_';

    public function __construct(
        private Client                              $esClient,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private ComputeProductsKeyIndicators $getProductsKeyIndicators
    ) {
    }

    public function execute(ProductIdCollection $productIdCollection): void
    {
        $params = [];
        $productModelsScores = $this->getProductModelScoresQuery->byProductModelIds($productIdCollection);
        $productModelsKeyIndicators = $this->getProductsKeyIndicators->compute($productIdCollection);

        foreach ($productIdCollection->toArray() as $productId) {
            $productModelId = $productId->toInt();
            if (!array_key_exists($productModelId, $productModelsScores)) {
                continue;
            }
            $productModelScores = $productModelsScores[$productModelId];
            $keyIndicators = $productModelsKeyIndicators[$productModelId] ?? [];

            $params[self::PRODUCT_MODEL_IDENTIFIER_PREFIX . $productModelId] = [
                'script' => [
                    'inline' => "ctx._source.data_quality_insights = params;",
                    'params' => [
                        'scores' => $productModelScores->toArrayIntRank(),
                        'key_indicators' => $keyIndicators
                    ],
                ]
            ];
        }

        $this->esClient->bulkUpdate(
            array_map(
                fn ($productModelId) => self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $productModelId,
                $productIdCollection->toArrayInt()
            ),
            $params
        );
    }
}
