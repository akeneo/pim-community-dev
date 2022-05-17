<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetScoresPropertyStrategy;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Webmozart\Assert\Assert;

final class GetRanksDistributionFromProductScoresQuery implements GetRanksDistributionFromProductScoresQueryInterface
{
    public function __construct(
        private Client                                 $elasticsearchClient,
        private GetCategoryChildrenCodesQueryInterface $getCategoryChildrenIdsQuery,
        private GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes,
        private GetScoresPropertyStrategy              $getScoresProperty,
    ) {
    }

    public function forWholeCatalog(\DateTimeImmutable $date): RanksDistributionCollection
    {
        $query = $this->buildRankDistributionQuery();

        if (empty($query['aggs'])) {
            return new RanksDistributionCollection([]);
        }

        $elasticsearchResult = $this->elasticsearchClient->search($query);

        return $this->hydrateToRankDistributionCollection($elasticsearchResult);
    }

    public function byCategory(CategoryCode $categoryCode, \DateTimeImmutable $date): RanksDistributionCollection
    {
        $categoryCodes = $this->getCategoryChildrenIdsQuery->execute($categoryCode);

        $query = $this->buildRankDistributionQuery();
        $query['query']['constant_score']['filter']['bool']['filter'][] = [
            'terms' => [
                'categories' => $categoryCodes
            ]
        ];
        $elasticsearchResult = $this->elasticsearchClient->search($query);
        return $this->hydrateToRankDistributionCollection($elasticsearchResult);
    }

    /**
     * It would be possible to calculate all consolidation scores from Elasticsearch
     * in one request, by using multi level aggregation:
     * {
     *     "aggs": {
     *        "fe_case.cs_CZ": {
     *            "terms": {
     *                "field": "data_quality_insights.scores.fe_case.cs_CZ"
     *            },
     *            "aggs": {
     *                "agg2": {
     *                    "terms": {
     *                        "field": "family.code"
     *                    }
     *                }
     *            }
     *        }
     *     }
     * }
     *
     * But actually, there is a limit of the number of buckets that we can return:
     * https://www.elastic.co/guide/en/elasticsearch/reference/7.13/search-settings.html#search-settings-max-buckets
     *
     * As it's possible to reach more than 80,000 combinations of family/locale/channel, it's a good compromise to loop over the families:
     * - it avoids to reach this ES limit
     * - performance is good enough for a background job
     * - it probably reduces the pressure over ES
     *
     */
    public function byFamily(FamilyCode $familyCode, \DateTimeImmutable $date): RanksDistributionCollection
    {
        $query = $this->buildRankDistributionQuery();
        $query['query']['constant_score']['filter']['bool']['filter'][] = [
            'term' => [
                'family.code' => (string)$familyCode
            ]
        ];
        $elasticsearchResult = $this->elasticsearchClient->search($query);

        return $this->hydrateToRankDistributionCollection($elasticsearchResult);
    }

    /**
     * cf hydrateToRankDistributionCollection to see an example of result
     */
    private function buildRankDistributionQuery(): array
    {
        $scoresProperty = ($this->getScoresProperty)();
        $channels = $this->getChannelCodeWithLocaleCodes->findAll();
        $elasticsearchAggs = [];
        foreach ($channels as ['channelCode' => $channelCode, 'localeCodes' => $localeCodes]) {
            foreach ($localeCodes as $localeCode) {
                $channelLocaleKey = "$channelCode.$localeCode";
                $elasticsearchAggs[$channelLocaleKey] = [
                    'terms' => [
                        'field' => "data_quality_insights.$scoresProperty.$channelLocaleKey"
                    ]
                ];
            }
        }

        // size = 0 to avoid to fill the cache
        //@see https://www.elastic.co/guide/en/elasticsearch/reference/7.13/search-aggregations.html#agg-caches
        return [
            'size' => 0,
            'aggs' => $elasticsearchAggs,
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'document_type' => ProductInterface::class
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            'track_total_hits' => false
        ];
    }

    /**
     * @param array $elasticsearchResult
     *
     * [
     *      "aggregations": [
     *          "ecommerce.fr_FR": [
     *              "doc_count_error_upper_bound": 0,
     *              "sum_other_doc_count": 0,
     *              "buckets": [
     *                  [
     *                      "key": 3,
     *                      "doc_count": 181682
     *                  ],
     *                  [
     *                      "key": 4,
     *                      "doc_count": 37952
     *                  ],
     *                  [
     *                      "key": 2,
     *                      "doc_count": 22844
     *                  ],
     *                  [
     *                      "key": 5,
     *                      "doc_count": 12846
     *                  ],
     *                  [
     *                      "key": 1,
     *                      "doc_count": 1012
     *                  ]
     *              ]
     *          ]
     *      ]
     * ]
     *
     */
    private function hydrateToRankDistributionCollection(array $elasticsearchResult): RanksDistributionCollection
    {
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
}
