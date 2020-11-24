<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

final class GetRanksDistributionFromProductScoresQuery implements GetRanksDistributionFromProductScoresQueryInterface
{
    private Connection $connection;

    private GetCategoryChildrenIdsQueryInterface $getCategoryChildrenIdsQuery;

    public function __construct(Connection $connection, GetCategoryChildrenIdsQueryInterface $getCategoryChildrenIdsQuery)
    {
        $this->connection = $connection;
        $this->getCategoryChildrenIdsQuery = $getCategoryChildrenIdsQuery;
    }

    public function forWholeCatalog(\DateTimeImmutable $date): RanksDistributionCollection
    {
        $productScoresQuery = <<<SQL
SELECT latest_eval.product_id, latest_eval.scores
FROM pim_data_quality_insights_product_score AS latest_eval
LEFT JOIN pim_data_quality_insights_product_score AS other_eval
    ON other_eval.product_id = latest_eval.product_id
    AND latest_eval.evaluated_at < other_eval.evaluated_at
    AND other_eval.evaluated_at <= :day
WHERE latest_eval.evaluated_at <= :day
    AND other_eval.evaluated_at IS NULL
SQL;

        $query = $this->buildRanksDistributionQuery($productScoresQuery);
        $statement = $this->connection->executeQuery($query, ['day' => $date->format('Y-m-d')], ['day' => \PDO::PARAM_STR]);

        $results = $statement->fetchColumn();
        if (null === $results || false === $results) {
            return new RanksDistributionCollection([]);
        }

        $ranks = json_decode($results, true);

        if (!is_array($ranks)) {
            throw new \RuntimeException('Something went wrong when fetching ranks distribution for the whole catalog');
        }

        return new RanksDistributionCollection($ranks);
    }

    public function byCategory(CategoryCode $categoryCode, \DateTimeImmutable $date): RanksDistributionCollection
    {
        $productScoresQuery = <<<SQL
SELECT DISTINCT latest_eval.product_id, latest_eval.scores
FROM pim_data_quality_insights_product_score AS latest_eval
    INNER JOIN pim_catalog_category_product cp ON cp.product_id = latest_eval.product_id
    LEFT JOIN pim_data_quality_insights_product_score AS other_eval
        ON other_eval.product_id = latest_eval.product_id
        AND latest_eval.evaluated_at < other_eval.evaluated_at
        AND other_eval.evaluated_at <= :day
 WHERE latest_eval.evaluated_at <= :day
   AND other_eval.evaluated_at IS NULL
   AND cp . category_id IN (:categories)
SQL;

        $query = $this->buildRanksDistributionQuery($productScoresQuery);
        $categoryIds = $this->getCategoryChildrenIdsQuery->execute($categoryCode);

        $statement = $this->connection->executeQuery(
            $query,
            [
                'day' => $date->format('Y-m-d'),
                'categories' => $categoryIds
            ],
            [
                'day' => \PDO::PARAM_STR,
                'categories' => Connection::PARAM_INT_ARRAY
            ]
        );

        $results = $statement->fetchColumn();
        if (null === $results || false === $results) {
            return new RanksDistributionCollection([]);
        }

        $ranks = json_decode($results, true);
        if (!is_array($ranks)) {
            throw new \RuntimeException(sprintf('Something went wrong when fetching ranks distribution for the category "%s"', $categoryCode));
        }

        return new RanksDistributionCollection($ranks);
    }

    public function byFamily(FamilyCode $familyCode, \DateTimeImmutable $date): RanksDistributionCollection
    {
        $productScoresQuery = <<<SQL
SELECT DISTINCT latest_eval.product_id, latest_eval.scores
FROM pim_data_quality_insights_product_score AS latest_eval
    INNER JOIN pim_catalog_product AS product ON product.id = latest_eval.product_id
    INNER JOIN pim_catalog_family AS family ON family.id = product.family_id
    LEFT JOIN pim_data_quality_insights_product_score AS other_eval
        ON other_eval.product_id = latest_eval.product_id
        AND latest_eval.evaluated_at < other_eval.evaluated_at
        AND other_eval.evaluated_at <= :day
WHERE latest_eval.evaluated_at <= :day
AND other_eval.evaluated_at IS NULL
AND family.code = :family_code
SQL;
        $query = $this->buildRanksDistributionQuery($productScoresQuery);

        $statement = $this->connection->executeQuery(
            $query,
            [
                'day' => $date->format('Y-m-d'),
                'family_code' => $familyCode
            ],
            [
                'day' => \PDO::PARAM_STR,
                'family_code' => \PDO::PARAM_STR
            ]
        );

        $results = $statement->fetchColumn();
        if (null === $results || false === $results) {
            return new RanksDistributionCollection([]);
        }

        $ranks = json_decode($results, true);
        if (!is_array($ranks)) {
            throw new \RuntimeException(sprintf('Something went wrong when fetching ranks distribution for the family "%s"', $familyCode));
        }

        return new RanksDistributionCollection($ranks);
    }

    /**
     * Build the main SQL query to aggregates the product scores per channel/locale
     */
    private function buildRanksDistributionQuery(string $productScoresQuery): string
    {
        return <<<SQL
SELECT JSON_OBJECTAGG(channel_code, locale_ranks) AS channel_locale_ranks FROM (
    SELECT channel_code, JSON_OBJECTAGG(locale_code, ranks) AS locale_ranks FROM (
        SELECT channel_code, locale_code, JSON_OBJECTAGG(CONCAT('rank_', `rank`), total) AS ranks FROM (
            SELECT channel_code, locale_code,
                JSON_UNQUOTE(json_extract(scores, concat('$."', channel_code ,'"."', locale_code,'".rank'))) AS `rank`,
                count(product_id) AS total
            FROM (
                    $productScoresQuery
                ) product_score
                CROSS JOIN (
                    SELECT channel.code AS channel_code, locale.code  AS locale_code
                    FROM pim_catalog_channel channel
                    JOIN pim_catalog_channel_locale pccl ON channel.id = pccl.channel_id
                    JOIN pim_catalog_locale locale ON pccl.locale_id = locale.id
                ) channels_locales
                WHERE JSON_CONTAINS_PATH(scores, 'one', concat('$."', channel_code ,'"."', locale_code,'"'))
            GROUP BY channel_code, locale_code, `rank`
        ) ranks
        GROUP BY channel_code, locale_code
    ) locales_ranks
    GROUP BY channel_code
) channels_locales_ranks
SQL;
    }
}
