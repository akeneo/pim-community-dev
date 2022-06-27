<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductModelIdsFromProductModelCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDataQualityInsightsPropertiesForProductModelProjection implements GetAdditionalPropertiesForProductModelProjectionInterface
{
    public function __construct(
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private GetProductModelIdsFromProductModelCodesQueryInterface $getProductModelIdsFromProductModelCodesQuery,
        private ComputeProductsKeyIndicators $getProductsKeyIndicators,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * @param array<string> $productModelCodes
     * @param array<string, mixed> $context
     *
     * @return array<string, array{data_quality_insights: array{scores: array}}>
     */
    public function fromProductModelCodes(array $productModelCodes, array $context = []): array
    {
        $productModelCodesIds = $this->getProductModelIdsFromProductModelCodesQuery->execute($productModelCodes);

        $productModelIdCollection = $this->idFactory->createCollection(array_map(fn ($id) => (string) $id, array_values($productModelCodesIds)));
        Assert::isInstanceOf($productModelIdCollection, ProductModelIdCollection::class);
        $productModelScores = $this->getProductModelScoresQuery->byProductModelIdCollection($productModelIdCollection);
        $productModelKeyIndicators = $this->getProductsKeyIndicators->compute($productModelIdCollection);

        $additionalProperties = [];
        foreach ($productModelCodesIds as $productModelCode => $productId) {
            $index = (string)$productId;
            $additionalProperties[$productModelCode] = [
                'data_quality_insights' => [
                    'scores' => isset($productModelScores[$index]) ? $productModelScores[$index]->allCriteria()->toArrayIntRank() : [],
                    'scores_partial_criteria' => isset($productModelScores[$index]) ? $productModelScores[$index]->partialCriteria()->toArrayIntRank() : [],
                    'key_indicators' => isset($productModelKeyIndicators[$index]) ? $productModelKeyIndicators[$index] : []
                ],
            ];
        }

        return $additionalProperties;
    }
}
