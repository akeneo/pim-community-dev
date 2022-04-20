<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public function byProductModelId(ProductEntityIdInterface $productId): ChannelLocaleRateCollection
    {
        $productModelIdCollection = $this->idFactory->createCollection([(string) $productId]);
        $productScores = $this->byProductModelIds($productModelIdCollection);

        return $productScores[(string)$productId] ?? new ChannelLocaleRateCollection();
    }

    public function byProductModelIds(ProductEntityIdCollection $productModelIds): array
    {
        if ($productModelIds->isEmpty()) {
            return [];
        }

        $query = <<<SQL
SELECT product_model_id, scores FROM pim_data_quality_insights_product_model_score 
WHERE product_model_id IN (:productModelIds);
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['productModelIds' => $productModelIds->toArrayString()],
            ['productModelIds' => Connection::PARAM_INT_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productsScores[$row['product_model_id']] = $this->hydrateScores($row['scores']);
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = \json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores);
    }
}
