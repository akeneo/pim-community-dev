<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetLatestProductScoresByIdentifiersQuery implements GetLatestProductScoresByIdentifiersQueryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byProductIdentifier(string $identifier): ChannelLocaleRateCollection
    {
        $productScores = $this->byProductIdentifiers([$identifier]);

        return $productScores[$identifier] ?? new ChannelLocaleRateCollection();
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $query = <<<SQL
SELECT product.identifier, latest_score.scores
FROM pim_catalog_product product
INNER JOIN pim_data_quality_insights_product_score AS latest_score ON latest_score.product_id = product.id
LEFT JOIN pim_data_quality_insights_product_score AS younger_score
    ON younger_score.product_id = latest_score.product_id
    AND younger_score.evaluated_at > latest_score.evaluated_at
WHERE product.identifier IN(:product_identifiers) 
  AND younger_score.evaluated_at IS NULL;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $productsScores = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productsScores[$row['identifier']] = $this->hydrateScores($row['scores']);
        }

        return $productsScores;
    }

    private function hydrateScores(string $rawScores): ChannelLocaleRateCollection
    {
        $scores = json_decode($rawScores, true, 512, JSON_THROW_ON_ERROR);

        return ChannelLocaleRateCollection::fromNormalizedRates($scores, fn (array $score) => $score['value']);
    }
}
