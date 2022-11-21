<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Webmozart\Assert\Assert;

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

    public function __invoke(ProductEntityIdCollection $entityIdCollection): void
    {
        switch ($this->documentType) {
            case ProductModelInterface::class:
                Assert::isInstanceOf($entityIdCollection, ProductModelIdCollection::class);
                $scores = $this->getProductModelScoresQuery->byProductModelIdCollection($entityIdCollection);
                $identifierPrefix = self::PRODUCT_MODEL_IDENTIFIER_PREFIX;
                break;
            case ProductInterface::class:
                Assert::isInstanceOf($entityIdCollection, ProductUuidCollection::class);
                $scores = $this->getProductScoresQuery->byProductUuidCollection($entityIdCollection);
                $identifierPrefix = self::PRODUCT_IDENTIFIER_PREFIX;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid type %s', $this->documentType));
        }

        $computedKeyIndicators = $this->computeProductsKeyIndicators->compute($entityIdCollection);

        $params = [];
        foreach ($entityIdCollection->toArray() as $entityId) {
            if (!array_key_exists((string) $entityId, $scores)) {
                continue;
            }
            $qualityScores = $scores[(string) $entityId];
            $keyIndicators = $computedKeyIndicators[(string) $entityId] ?? [];

            $params[$identifierPrefix . $entityId] = [
                'script' => [
                    'source' => "ctx._source.data_quality_insights = params;",
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
                $entityIdCollection->toArrayString()
            ),
            $params
        );
    }
}
