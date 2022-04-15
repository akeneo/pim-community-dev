<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDataQualityInsightsPropertiesForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery,
        private ComputeProductsKeyIndicators $getProductsKeyIndicators,
    ) {
    }

    /**
     * @param array<string> $productIdentifiers
     * @param array<string, mixed> $context
     *
     * @return array<string, array{data_quality_insights: array{scores: array, key_indicators: array}}>
     */
    public function fromProductIdentifiers(array $productIdentifiers, array $context = []): array
    {
        $productIdentifierIds = $this->getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers);

        $productIdCollection = ProductIdCollection::fromProductIds(array_values($productIdentifierIds));
        $productScores = $this->getProductScoresQuery->byProductIds($productIdCollection);
        $productKeyIndicators = $this->getProductsKeyIndicators->compute($productIdCollection);

        $additionalProperties = [];
        foreach ($productIdentifierIds as $productIdentifier => $productId) {
            $productId = $productId->toInt();
            $additionalProperties[$productIdentifier] = [
                'data_quality_insights' => [
                    'scores' => isset($productScores[$productId]) ? $productScores[$productId]->allCriteria()->toArrayIntRank() : [],
                    'scores_partial_criteria' => isset($productScores[$productId]) ? $productScores[$productId]->partialCriteria()->toArrayIntRank() : [],
                    'key_indicators' => isset($productKeyIndicators[$productId]) ? $productKeyIndicators[$productId] : []
                ],
            ];
        }

        return $additionalProperties;
    }
}
