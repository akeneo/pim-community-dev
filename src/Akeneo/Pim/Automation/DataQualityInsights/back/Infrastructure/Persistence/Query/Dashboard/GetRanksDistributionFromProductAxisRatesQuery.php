<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductAxisRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

final class GetRanksDistributionFromProductAxisRatesQuery implements GetRanksDistributionFromProductAxisRatesQueryInterface
{
    /** @var Connection */
    private $connection;

    /** @var GetCategoryChildrenIdsQueryInterface */
    private $getCategoryChildrenIdsQuery;

    public function __construct(Connection $connection, GetCategoryChildrenIdsQueryInterface $getCategoryChildrenIdsQuery)
    {
        $this->connection = $connection;
        $this->getCategoryChildrenIdsQuery = $getCategoryChildrenIdsQuery;
    }

    public function forWholeCatalog(\DateTimeImmutable $date): RanksDistributionCollection
    {
        $productAxisRatesQuery = <<<SQL
SELECT latest_eval.axis_code, latest_eval.product_id, latest_eval.rates
FROM pim_data_quality_insights_product_axis_rates AS latest_eval
    LEFT JOIN pim_data_quality_insights_product_axis_rates AS other_eval
        ON other_eval.axis_code = latest_eval.axis_code
        AND other_eval.product_id = latest_eval.product_id
        AND latest_eval.evaluated_at < other_eval.evaluated_at
        AND other_eval.evaluated_at <= :day
WHERE latest_eval.evaluated_at <= :day
    AND other_eval.evaluated_at IS NULL
SQL;

        $query = $this->buildRanksDistributionQuery($productAxisRatesQuery);
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
        $productAxisRatesQuery = <<<SQL
SELECT DISTINCT latest_eval.axis_code, latest_eval.product_id, latest_eval.rates
FROM pim_data_quality_insights_product_axis_rates AS latest_eval
    INNER JOIN pim_catalog_category_product cp ON cp.product_id = latest_eval.product_id
    LEFT JOIN pim_data_quality_insights_product_axis_rates AS other_eval
        ON other_eval.axis_code = latest_eval.axis_code
        AND other_eval.product_id = latest_eval.product_id
        AND latest_eval.evaluated_at < other_eval.evaluated_at
        AND other_eval.evaluated_at <= :day
WHERE latest_eval.evaluated_at <= :day
    AND other_eval.evaluated_at IS NULL
    AND cp . category_id IN (:categories)
SQL;

        $query = $this->buildRanksDistributionQuery($productAxisRatesQuery);
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
        $productAxisRatesQuery = <<<SQL
SELECT DISTINCT latest_eval.axis_code, latest_eval.product_id, latest_eval.rates
FROM pim_data_quality_insights_product_axis_rates AS latest_eval
    INNER JOIN pim_catalog_product AS product ON product.id = latest_eval.product_id
    INNER JOIN pim_catalog_family AS family ON family.id = product.family_id
    LEFT JOIN pim_data_quality_insights_product_axis_rates AS other_eval
        ON other_eval.axis_code = latest_eval.axis_code
        AND other_eval.product_id = latest_eval.product_id
        AND latest_eval.evaluated_at < other_eval.evaluated_at
        AND other_eval.evaluated_at <= :day
WHERE latest_eval.evaluated_at <= :day
    AND other_eval.evaluated_at IS NULL
    AND family.code = :family_code
SQL;
        $query = $this->buildRanksDistributionQuery($productAxisRatesQuery);

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
     * Build the main SQL query to aggregates the product axis rates per axis/channel/locale
     */
    private function buildRanksDistributionQuery(string $productAxisRatesQuery): string
    {
        return <<<SQL
SELECT JSON_OBJECTAGG(axis_code, channel_locale_ranks) FROM (
    SELECT axis_code, JSON_OBJECTAGG(channel_code, locale_ranks) AS channel_locale_ranks FROM (
        SELECT axis_code, channel_code, JSON_OBJECTAGG(locale_code, ranks) AS locale_ranks FROM (
            SELECT axis_code, channel_code, locale_code, JSON_OBJECTAGG(CONCAT('rank_', `rank`), total) AS ranks FROM (
                SELECT axis_code, channel_code, locale_code,
                    JSON_UNQUOTE(json_extract(rates, concat('$."', channel_code ,'"."', locale_code,'".rank'))) AS `rank`,
                    count(product_id) AS total
                FROM (
                    $productAxisRatesQuery
                ) product_axis_rates
                CROSS JOIN (
                    SELECT channel.code AS channel_code, locale.code  AS locale_code
                    FROM pim_catalog_channel channel
                    JOIN pim_catalog_channel_locale pccl ON channel.id = pccl.channel_id
                    JOIN pim_catalog_locale locale ON pccl.locale_id = locale.id
                ) channels_locales
                WHERE JSON_CONTAINS_PATH(rates, 'one', concat('$."', channel_code ,'"."', locale_code,'"'))
                GROUP BY axis_code, channel_code, locale_code, `rank`
            ) ranks
            GROUP BY axis_code, channel_code, locale_code
        ) locales_ranks
        GROUP BY axis_code, channel_code
    ) channels_locales_ranks
    GROUP BY axis_code
) axes_channels_locales_ranks
SQL;
    }
}
