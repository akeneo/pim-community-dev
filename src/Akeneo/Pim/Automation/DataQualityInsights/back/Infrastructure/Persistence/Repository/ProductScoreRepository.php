<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Example of JSON string stored in the column "scores":
 * {
 *    "mobile": {
 *      "en_US": {
 *        "rank": 1,
 *        "value": 96
 *      }
 *    },
 *    "ecommerce": {
 *      "en_US": {
 *        "rank": 2,
 *        "value": 82
 *      },
 *      "fr_FR": {
 *        "rank": 5,
 *        "value": 32
 *      }
 *    }
 *  }
 */
final class ProductScoreRepository implements ProductScoreRepositoryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $productsScores): void
    {
        if (empty($productsScores)) {
            return;
        }

        $insertQueries = '';
        $insertQueriesParameters = [];
        $insertQueriesParametersTypes = [];

        $deleteQueries = '';
        $deleteQueriesParameters = [];
        $deleteQueriesParametersTypes = [];

        foreach ($productsScores as $index => $productScore) {
            Assert::isInstanceOf($productScore, Write\ProductScores::class);
            $productId = sprintf('productId_%d', $index);
            $evaluatedAt = sprintf('evaluatedAt_%d', $index);
            $scores = sprintf('scores_%d', $index);

            $insertQueries .= <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores)
VALUES (:$productId, :$evaluatedAt, :$scores)
ON DUPLICATE KEY UPDATE evaluated_at = :$evaluatedAt, scores = :$scores;
SQL;

            $insertQueriesParameters[$productId] = $productScore->getProductId()->toInt();
            $insertQueriesParametersTypes[$productId] = \PDO::PARAM_INT;
            $insertQueriesParameters[$evaluatedAt] = $productScore->getEvaluatedAt()->format('Y-m-d');
            $insertQueriesParameters[$scores] = \json_encode($productScore->getScores()->toNormalizedRates());

            // We need to delete younger product scores after inserting the new ones,
            // so we insure to have 1 product score per product
            $deleteQueries .= <<<SQL
DELETE old_scores
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_id = old_scores.product_id
    AND younger_scores.evaluated_at > old_scores.evaluated_at
WHERE old_scores.product_id = :$productId
AND old_scores.evaluated_at < :$evaluatedAt;
SQL;

            $deleteQueriesParameters[$productId] = $productScore->getProductId()->toInt();
            $deleteQueriesParametersTypes[$productId] = \PDO::PARAM_INT;
            $deleteQueriesParameters[$evaluatedAt] = $productScore->getEvaluatedAt()->format('Y-m-d');
        }

        $this->dbConnection->executeQuery($insertQueries, $insertQueriesParameters, $insertQueriesParametersTypes);
        $this->dbConnection->executeQuery($deleteQueries, $deleteQueriesParameters, $deleteQueriesParametersTypes);
    }

    public function purgeUntil(\DateTimeImmutable $date): void
    {
        $query = <<<SQL
DELETE old_scores
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_id = old_scores.product_id
    AND younger_scores.evaluated_at > old_scores.evaluated_at
WHERE old_scores.evaluated_at < :purge_date;
SQL;

        $this->dbConnection->executeQuery(
            $query,
            ['purge_date' => $date->format('Y-m-d')]
        );
    }
}
