<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDataQualityInsightsPropertiesForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private ComputeProductsKeyIndicators $getProductsKeyIndicators,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * @param array<UuidInterface> $productUuids
     * @param array<string, mixed> $context
     *
     * @return array<string, array{data_quality_insights: array{scores: array, key_indicators: array}}>
     */
    public function fromProductUuids(array $productUuids, array $context = []): array
    {
        $productUuidCollection = $this->idFactory->createCollection(array_map(fn (UuidInterface $productUuid) => $productUuid->toString(), array_values($productUuids)));
        Assert::isInstanceOf($productUuidCollection, ProductUuidCollection::class);
        $productScores = $this->getProductScoresQuery->byProductUuidCollection($productUuidCollection);
        $productKeyIndicators = $this->getProductsKeyIndicators->compute($productUuidCollection);

        $additionalProperties = [];
        foreach ($productUuidCollection as $productUuid) {
            $index = (string) $productUuid;
            $additionalProperties[$index] = [
                'data_quality_insights' => [
                    'scores' => isset($productScores[$index]) ? $productScores[$index]->allCriteria()->toArrayIntRank() : [],
                    'scores_partial_criteria' => isset($productScores[$index]) ? $productScores[$index]->partialCriteria()->toArrayIntRank() : [],
                    'key_indicators' => isset($productKeyIndicators[$index]) ? $productKeyIndicators[$index] : []
                ],
            ];
        }

        return $additionalProperties;
    }
}
