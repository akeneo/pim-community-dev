<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkUpdateProductQualityScoresIndex implements BulkUpdateProductQualityScoresInterface
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

    public function __invoke(ProductEntityIdCollection $productIdCollection): void
    {
        switch ($this->documentType) {
            case ProductModelInterface::class:
                $scores = $this->getProductModelScoresQuery->byProductModelIds($productIdCollection);
                $identifierPrefix = self::PRODUCT_MODEL_IDENTIFIER_PREFIX;
                break;
            case ProductInterface::class:
                $scores = $this->getProductScoresQuery->byProductIds($productIdCollection);
                $identifierPrefix = self::PRODUCT_IDENTIFIER_PREFIX;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid type %s', $this->documentType));
        }

        $computedKeyIndicators = $this->computeProductsKeyIndicators->compute($productIdCollection);

        $params = [];
        foreach ($productIdCollection->toArray() as $productId) {
            if (!array_key_exists((string) $productId, $scores)) {
                continue;
            }
            $qualityScores = $scores[(string) $productId];
            $keyIndicators = $computedKeyIndicators[(string) $productId] ?? [];

            $params[$identifierPrefix . $productId] = [
                'script' => [
                    'inline' => "ctx._source.data_quality_insights = params;",
                    'params' => [
                        'scores' => $qualityScores->allCriteria()->toArrayIntRank(),
                        'scores_partial_criteria' => $qualityScores->partialCriteria()->toArrayIntRank(),
                        'key_indicators' => $keyIndicators
                    ],
                ]
            ];
        }

        $this->esClient->bulkUpdate(
            array_map(
                fn ($productId) => $identifierPrefix . $productId,
                $productIdCollection->toArrayString()
            ),
            $params
        );
    }
}
