<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkUpdateProductsIndex implements BulkUpdateProductsInterface
{
    private const PRODUCT_IDENTIFIER_PREFIX = 'product_';
    private const PRODUCT_MODEL_IDENTIFIER_PREFIX = 'product_model_';

    public function __construct(
        private Client $esClient,
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private ComputeProductsKeyIndicators $computeProductsKeyIndicators,
        private string $documentType
    ) {
    }

    public function __invoke(ProductIdCollection $productIdCollection): void
    {
        if ($this->documentType === ProductModelInterface::class) {
            $scores = $this->getProductModelScoresQuery->byProductModelIds($productIdCollection);
            $computedKeyIndicators = $this->computeProductsKeyIndicators->compute($productIdCollection);
            $identifierPrefix = self::PRODUCT_MODEL_IDENTIFIER_PREFIX;
        } else {
            $scores = $this->getProductScoresQuery->byProductIds($productIdCollection);
            $computedKeyIndicators = $this->computeProductsKeyIndicators->compute($productIdCollection);
            $identifierPrefix = self::PRODUCT_IDENTIFIER_PREFIX;
        }

        $params = [];
        foreach ($productIdCollection->toArray() as $productId) {
            $productId = $productId->toInt();
            if (!array_key_exists($productId, $scores)) {
                continue;
            }
            $productScores = $scores[$productId];
            $keyIndicators = $computedKeyIndicators[$productId] ?? [];

            $params[$identifierPrefix . $productId] = [
                'script' => [
                    'inline' => "ctx._source.data_quality_insights = params;",
                    'params' => [
                        'scores' => $productScores->toArrayIntRank(),
                        'key_indicators' => $keyIndicators
                    ],
                ]
            ];
        }

        $this->esClient->bulkUpdate(
            array_map(
                fn ($productId) => $identifierPrefix . (string) $productId,
                $productIdCollection->toArrayInt()
            ),
            $params
        );
    }
}
