<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(private Connection $dbConnection)
    {
    }

    public function byProductModelId(ProductId $productId): ChannelLocaleRateCollection
    {
        $productScores = $this->byProductIdCollection(ProductIdCollection::fromProductId($productId));

        return $productScores[$productId->toInt()] ?? new ChannelLocaleRateCollection();
    }

    public function byProductIdCollection(ProductIdCollection $productIdCollection): array
    {
        if ($productIdCollection->isEmpty()) {
            return [];
        }

        $query = <<<SQL
SELECT score.product_model_id, score.scores
FROM pim_data_quality_insights_product_model_score AS score
WHERE score.product_model_id IN(:product_ids);
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_ids' => $productIdCollection->toArrayInt()],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetchAssociative()) {
            $productId = (int) $row['product_id'];
            $productsScores[$productId] = $this->hydrateScores($row['scores']);
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores, fn (array $score) => $score['value']);
    }
}
