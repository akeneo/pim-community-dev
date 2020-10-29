<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDataQualityInsightsPropertiesForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
{
    private GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery;

    private GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery;

    private ComputeProductsKeyIndicators $getProductsKeyIndicators;

    public function __construct(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery,
        ComputeProductsKeyIndicators $getProductsKeyIndicators
    ) {
        $this->getProductIdsFromProductIdentifiersQuery = $getProductIdsFromProductIdentifiersQuery;
        $this->getLatestProductAxesRanksQuery = $getLatestProductAxesRanksQuery;
        $this->getProductsKeyIndicators = $getProductsKeyIndicators;
    }

    /**
     * @inheritDoc
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $productIds = $this->getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers);
        $productAxesRanks = $this->getLatestProductAxesRanksQuery->byProductIds($productIds);
        $productKeyIndicators = $this->getProductsKeyIndicators->compute($productIds);

        $additionalProperties = [];
        foreach ($productIds as $productIdentifier => $productId) {
            $productId = $productId->toInt();
            $additionalProperties[$productIdentifier] = [
                'rates' => isset($productAxesRanks[$productId]) ? $productAxesRanks[$productId]->toArrayInt() : [],
                'data_quality_insights' => ['key_indicators' => isset($productKeyIndicators[$productId]) ? $productKeyIndicators[$productId] : []],
            ];
        }

        return $additionalProperties;
    }
}
