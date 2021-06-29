<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetLatestProductScoresQuery implements GetLatestProductScoresQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byProductId(ProductId $productId): ChannelLocaleRateCollection
    {
        $productScores = $this->byProductIds([$productId]);

        return $productScores[$productId->toInt()] ?? new ChannelLocaleRateCollection();
    }

    public function byProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $productIds = array_map(fn (ProductId $productId) => $productId->toInt(), $productIds);

        $query = <<<SQL
SELECT latest_score.product_id, latest_score.scores
FROM pim_data_quality_insights_product_score AS latest_score
    LEFT JOIN pim_data_quality_insights_product_score AS younger_score
        ON younger_score.product_id = latest_score.product_id
        AND younger_score.evaluated_at > latest_score.evaluated_at
WHERE latest_score.product_id IN(:product_ids)
    AND younger_score.evaluated_at IS NULL;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_ids' => $productIds],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productId = intval($row['product_id']);
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
