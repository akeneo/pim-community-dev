<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Elasticsearch\QueryString;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    private const INDEX_TYPE = 'pimee_reference_entity_record';

    /** @var Client */
    private $recordClient;

    /**
     * @param Client $recordClient
     */
    public function __construct(Client $recordClient)
    {
        $this->recordClient = $recordClient;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RecordQuery $recordQuery): IdentifiersForQueryResult
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($recordQuery);
        $matches = $this->recordClient->search(self::INDEX_TYPE, $elasticSearchQuery);
        $identifiers = array_map(function (array $hit) {
            return $hit['_id'];
        }, $matches['hits']['hits']);

        $queryResult = new IdentifiersForQueryResult();
        $queryResult->identifiers = $identifiers;
        $queryResult->total = $matches['hits']['total'];

        return $queryResult;
    }

    private function getElasticSearchQuery(RecordQuery $recordQuery): array
    {
        $referenceEntityCode = $recordQuery->getFilter('reference_entity');
        $fullTextFilter = ($recordQuery->hasFilter('full_text')) ? $recordQuery->getFilter('full_text') : null;
        $codeLabelFilter = ($recordQuery->hasFilter('code_label')) ? $recordQuery->getFilter('code_label') : null;
        $codeFilter = ($recordQuery->hasFilter('code')) ? $recordQuery->getFilter('code') : null;
        $query = [
            '_source' => '_id',
            'from' => $recordQuery->getSize() * $recordQuery->getPage(),
            'size' => $recordQuery->getSize(),
            'sort' => ['updated_at' => 'desc'],
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'reference_entity_code' => $referenceEntityCode['value'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (null !== $fullTextFilter && !empty($fullTextFilter['value'])) {
            $terms = $this->getTerms($fullTextFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf('record_full_text_search.%s.%s', $recordQuery->getchannel(), $recordQuery->getlocale()),
                    'query'         => $terms
                ],
            ];
        }

        if (null !== $codeLabelFilter && !empty($codeLabelFilter['value'])) {
            $terms = $this->getTerms($codeLabelFilter);
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                'query_string' => [
                    'default_field' => sprintf('record_code_label_search.%s', $recordQuery->getlocale()),
                    'query'         => $terms
                ],
            ];
        }

        if (null !== $codeFilter && !empty($codeFilter['value']) && 'NOT IN' === $codeFilter['operator']) {
            $query['query']['constant_score']['filter']['bool']['must_not'][] = [
                'terms' => [
                    'code' => $codeFilter['value']
                ]
            ];
        }

        if (null !== $codeFilter && !empty($codeFilter['value']) && 'IN' === $codeFilter['operator']) {
            $query['query']['constant_score']['filter']['bool']['must'][] = [
                'terms' => [
                    'code' => $codeFilter['value']
                ]
            ];
        }

        return $query;
    }

    private function getTerms(array $searchFilter): string
    {
        $loweredTerms = strtolower($searchFilter['value']);
        $terms = explode(' ', $loweredTerms);
        $wildcardTerms = array_map(function (string $term) {
            return sprintf('*%s*', QueryString::escapeValue($term));
        }, $terms);
        $query = implode(' AND ', $wildcardTerms);

        return $query;
    }
}
