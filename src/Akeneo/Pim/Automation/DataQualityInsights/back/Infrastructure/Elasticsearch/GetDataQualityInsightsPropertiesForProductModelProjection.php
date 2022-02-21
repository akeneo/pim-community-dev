<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductModelIdsFromProductModelCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDataQualityInsightsPropertiesForProductModelProjection implements GetAdditionalPropertiesForProductModelProjectionInterface
{
    public function __construct(
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private GetProductModelIdsFromProductModelCodesQueryInterface $getProductModelIdsFromProductModelCodesQuery
    ) {
    }

    public function fromProductModelCodes(array $productModelCodes, array $context = []): array
    {
        $productModelCodesIds = $this->getProductModelIdsFromProductModelCodesQuery->execute($productModelCodes);

        $productIdCollection = ProductIdCollection::fromProductIds(array_values($productModelCodesIds));
        $productScores = $this->getProductModelScoresQuery->byProductModelIds($productIdCollection);

        $additionalProperties = [];
        foreach ($productModelCodesIds as $productModelCode => $productId) {
            $productId = $productId->toInt();
            $additionalProperties[$productModelCode] = [
                'data_quality_insights' => [
                    'scores' => isset($productScores[$productId]) ? $productScores[$productId]->toArrayIntRank() : [],
                ],
            ];
        }

        return $additionalProperties;
    }
}
