<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
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

    public function saveAll(array $productsScores): void
    {
        if (empty($productsScores)) {
            return;
        }

        $queries = '';
        $queriesParameters = [];
        $queriesParametersTypes = [];

        /** @var Write\ProductScores $productScore */
        foreach ($productsScores as $index => $productScore) {
            Assert::isInstanceOf($productScore, Write\ProductScores::class);
            $productId = sprintf('productId_%d', $index);
            $evaluatedAt = sprintf('evaluatedAt_%d', $index);
            $scores = sprintf('scores_%d', $index);

            $queries .= <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores)
VALUES (:$productId, :$evaluatedAt, :$scores)
ON DUPLICATE KEY UPDATE evaluated_at = :$evaluatedAt, scores = :$scores;
SQL;
            $queriesParameters[$productId] = $productScore->getProductId()->toInt();
            $queriesParametersTypes[$productId] = \PDO::PARAM_INT;
            $queriesParameters[$evaluatedAt] = $productScore->getEvaluatedAt()->format('Y-m-d');
            $queriesParameters[$scores] = $this->formatScores($productScore->getScores());
        }

        $this->dbConnection->executeQuery($queries, $queriesParameters, $queriesParametersTypes);
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

    private function formatScores(ChannelLocaleRateCollection $scores): string
    {
        $formattedScores = $scores->mapWith(function (Rate $score) {
            return [
                'rank' => Rank::fromRate($score)->toInt(),
                'value' => $score->toInt(),
            ];
        });

        return json_encode($formattedScores);
    }
}
