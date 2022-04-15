<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(
        private Connection $dbConnection
    ) {
    }

    public function byProductModelId(ProductId $productId): Read\Scores
    {
        $productScores = $this->byProductModelIds(ProductIdCollection::fromProductId($productId));

        return $productScores[$productId->toInt()] ?? new Read\Scores(
            new ChannelLocaleRateCollection(),
            new ChannelLocaleRateCollection()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function byProductModelIds(ProductIdCollection $productModelIds): array
    {
        if ($productModelIds->isEmpty()) {
            return [];
        }

        $query = <<<SQL
SELECT product_model_id, scores, scores_partial_criteria FROM pim_data_quality_insights_product_model_score 
WHERE product_model_id IN (:productModelIds);
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['productModelIds' => $productModelIds->toArrayInt()],
            ['productModelIds' => Connection::PARAM_INT_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productModelId = \intval($row['product_model_id']);
            $productsScores[$productModelId] = new Read\Scores(
                $this->hydrateScores($row['scores']),
                $this->hydrateScores($row['scores_partial_criteria'] ?? '{}')
            );
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = \json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores);
    }
}
