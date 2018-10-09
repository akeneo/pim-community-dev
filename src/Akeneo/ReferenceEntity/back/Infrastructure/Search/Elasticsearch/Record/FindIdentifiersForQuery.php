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

use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

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

    private function getElasticSearchQuery(RecordQuery $recordQuery)
    {
        $searchFilter = $recordQuery->getFilter('search');
        $referenceEntityCode = $recordQuery->getFilter('reference_entity');

        $query = [
            '_source' => '_id',
            'from' => $recordQuery->getSize() * $recordQuery->getPage(),
            'size' => $recordQuery->getSize(),
            'sort' => ['identifier' => 'asc'],
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

        if (!empty($searchFilter['value'])) {
            foreach (explode(' ', $searchFilter['value']) as $term) {
                $query['query']['constant_score']['filter']['bool']['filter'][] = [
                        'query_string' => [
                            'default_field' => sprintf('record_list_search.%s.%s', $recordQuery->getChannel(), $recordQuery->getLocale()),
                            'query'         => sprintf('*%s*', strtolower($term)),
                        ],
                    ];
            }
        }

        return $query;
    }
}
