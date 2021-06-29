<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDataQualityInsightsPropertiesForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
{
    private GetLatestProductScoresQueryInterface $getProductScoresQuery;

    private GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery;

    private ComputeProductsKeyIndicators $getProductsKeyIndicators;

    public function __construct(
        GetLatestProductScoresQueryInterface $getProductScoresQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery,
        ComputeProductsKeyIndicators $getProductsKeyIndicators
    ) {
        $this->getProductScoresQuery = $getProductScoresQuery;
        $this->getProductIdsFromProductIdentifiersQuery = $getProductIdsFromProductIdentifiersQuery;
        $this->getProductsKeyIndicators = $getProductsKeyIndicators;
    }

    /**
     * @inheritDoc
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $productIds = $this->getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers);
        $productScores = $this->getProductScoresQuery->byProductIds($productIds);
        $productKeyIndicators = $this->getProductsKeyIndicators->compute($productIds);

        $additionalProperties = [];
        foreach ($productIds as $productIdentifier => $productId) {
            $productId = $productId->toInt();
            $additionalProperties[$productIdentifier] = [
                'data_quality_insights' => [
                    'scores' => isset($productScores[$productId]) ? $productScores[$productId]->toArrayIntRank() : [],
                    'key_indicators' => isset($productKeyIndicators[$productId]) ? $productKeyIndicators[$productId] : []
                ],
            ];
        }

        return $additionalProperties;
    }
}
