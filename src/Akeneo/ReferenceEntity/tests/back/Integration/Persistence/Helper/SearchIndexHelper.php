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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Helper;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * This class is responsible for helping in the elasticsearch index setup in tests.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchIndexHelper
{
    /** @var Client */
    private $recordClient;

    private const INDEX_TYPE = 'pimee_reference_entity_record';

    public function __construct(Client $recordClient)
    {
        $this->recordClient = $recordClient;
    }

    public function resetIndex(): void
    {
        $this->recordClient->resetIndex();
    }

    public function index(array $records): void
    {
        foreach ($records as $record) {
            if (!array_key_exists('identifier', $record)) {
                throw new \InvalidArgumentException('Expect to index record with a "identifier" property. None found.');
            }

            $this->recordClient->index(self::INDEX_TYPE, $record['identifier'], $record);
        }

        $this->recordClient->refreshIndex();
    }

    public function search(string $referenceEntityCode, string $channel, string $locale, array $terms): array
    {
        $query = $this->getQuery($referenceEntityCode, $channel, $locale, $terms);
        $matchingIdentifiers = $this->executeQuery($query);

        return $matchingIdentifiers;
    }

    public function executeQuery(array $query): array
    {
        $matches = $this->recordClient->search(self::INDEX_TYPE, $query);
        $documents = $matches['hits']['hits'] ?? [];

        $matchingIdentifiers = [];
        foreach ($documents as $document) {
            $matchingIdentifiers[] = $document['_id'];
        }

        return $matchingIdentifiers;
    }

    private function getQuery(string $referenceEntityCode, $channel, $locale, array $terms): array
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'reference_entity_code' => $referenceEntityCode,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($terms as $term) {
            $query['query']['constant_score']['filter']['bool']['filter'][] = [
                    'query_string' => [
                        'default_field' => sprintf('record_list_search.%s.%s', $channel, $locale),
                        'query'         => sprintf('*%s*', $term),
                    ],
                ];
        }

        return $query;
    }
}
