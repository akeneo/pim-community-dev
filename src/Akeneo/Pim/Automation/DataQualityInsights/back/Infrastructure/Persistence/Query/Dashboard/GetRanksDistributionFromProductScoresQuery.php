<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

final class GetRanksDistributionFromProductScoresQuery implements GetRanksDistributionFromProductScoresQueryInterface
{
    private Connection $connection;

    private Client $elasticsearchClient;

    private GetCategoryChildrenIdsQueryInterface $getCategoryChildrenIdsQuery;

    private GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes;

    public function __construct(
        Connection $connection,
        Client $elasticsearchClient,
        GetCategoryChildrenIdsQueryInterface $getCategoryChildrenIdsQuery,
        GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes
    ) {
        $this->connection = $connection;
        $this->elasticsearchClient = $elasticsearchClient;
        $this->getCategoryChildrenIdsQuery = $getCategoryChildrenIdsQuery;
        $this->getChannelCodeWithLocaleCodes = $getChannelCodeWithLocaleCodes;
    }

    public function forWholeCatalog(\DateTimeImmutable $date): RanksDistributionCollection
    {
        $channels = $this->getChannelCodeWithLocaleCodes->findAll();
        $elasticsearchAggs = [];
        foreach ($channels as ['channelCode' => $channelCode, 'localeCodes' => $localeCodes]) {
            foreach ($localeCodes as $localeCode) {
                $channelLocaleKey = "$channelCode.$localeCode";
                $elasticsearchAggs[$channelLocaleKey] = [ 'terms' => ['field' => "data_quality_insights.scores.$channelLocaleKey"]];
            }
        }

        if (empty($elasticsearchAggs)) {
            return new RanksDistributionCollection([]);
        }

        // size = 0 to avoid to fill the cache
        //@see https://www.elastic.co/guide/en/elasticsearch/reference/7.13/search-aggregations.html#agg-caches
        $elasticsearchResult = $this->elasticsearchClient->search(
            [
                'aggs' => $elasticsearchAggs,
                'size' => 0,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'document_type' => ProductInterface::class
                            ]
                        ]
                    ],
                ],
                'track_total_hits' => false
            ]
        );

        Assert::keyExists($elasticsearchResult, 'aggregations');
        $ranks = [];
        foreach ($elasticsearchResult["aggregations"] as $channelLocaleKey => $aggregationPerRank) {
            [$channelCode, $localeCode] = explode('.', $channelLocaleKey);

            Assert::keyExists($aggregationPerRank, 'buckets');
            foreach ($aggregationPerRank['buckets'] as ['key' => $rank, 'doc_count' => $numberOfProducts]) {
                $ranks[$channelCode][$localeCode]["rank_$rank"] = $numberOfProducts;
            }
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
     *
     * return [
     *      "ecommerce" => [
     *          "en_US" => [
     *              rank_1 => 100,
     *              rank_2 => 200,
     *          ]
     *      ]
     * ]
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
