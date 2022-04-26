<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
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

        $insertValues = implode(', ', array_map(function (Write\ProductScores $productScore) {
            $productUuid = $productScore->getEntityId();
            Assert::isInstanceOf($productUuid, ProductUuid::class);

            return sprintf(
                "(%s, '%s', '%s', '%s')",
                $productUuid->toBytes(),
                $productScore->getEvaluatedAt()->format('Y-m-d'),
                \json_encode($productScore->getScores()->toNormalizedRates()),
                \json_encode($productScore->getScoresPartialCriteria()->toNormalizedRates())
            );
        }, $productsScores));

        $this->dbConnection->executeQuery(
            <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_uuid, evaluated_at, scores, scores_partial_criteria) 
VALUES $insertValues AS product_score_values
ON DUPLICATE KEY UPDATE 
    evaluated_at = product_score_values.evaluated_at, 
    scores = product_score_values.scores, 
    scores_partial_criteria = product_score_values.scores_partial_criteria;
SQL
        );

        // We need to delete younger product scores after inserting the new ones,
        // so we insure to have 1 product score per product
        $deleteValues = implode(', ', array_map(fn (Write\ProductScores $productScore) => (string) $productScore->getEntityId()->toBytes(), $productsScores));

        $this->dbConnection->executeQuery(
            <<<SQL
DELETE old_scores
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_uuid = old_scores.product_uuid
    AND younger_scores.evaluated_at > old_scores.evaluated_at
WHERE old_scores.product_uuid IN ($deleteValues);
SQL
        );
    }

    public function purgeUntil(\DateTimeImmutable $date): void
    {
        $query = <<<SQL
DELETE old_scores
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_uuid = old_scores.product_uuid
    AND younger_scores.evaluated_at > old_scores.evaluated_at
WHERE old_scores.evaluated_at < :purge_date;
SQL;

        $this->dbConnection->executeQuery(
            $query,
            ['purge_date' => $date->format('Y-m-d')]
        );
    }
}
