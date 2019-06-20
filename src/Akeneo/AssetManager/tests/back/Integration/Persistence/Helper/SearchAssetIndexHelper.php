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
use PHPUnit\Framework\Assert;

/**
 * This class is responsible for helping in the elasticsearch index setup in tests.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchRecordIndexHelper
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
        $this->refreshIndex();

        $query = $this->getQuery($referenceEntityCode, $channel, $locale, $terms);
        $matchingIdentifiers = $this->executeQuery($query);

        return $matchingIdentifiers;
    }

    public function findRecordsByReferenceEntity(string $referenceEntityCode): array
    {
        $this->refreshIndex();

        $query = [
            '_source' => '_id',
            'query' => [
                'match' => ['reference_entity_code' => $referenceEntityCode,],
            ],
        ];
        $matchingIdentifiers = $this->executeQuery($query);

        return $matchingIdentifiers;
    }

    public function findRecord(string $referenceEntityCode, string $recordCode): array
    {
        $this->refreshIndex();

        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['reference_entity_code' => $referenceEntityCode]],
                        ['term' => ['code' => $recordCode]],
                    ],
                ],
            ],
        ];
        $matchingIdentifiers = $this->executeQuery($query);

        return $matchingIdentifiers;
    }

    public function assertRecordExists(string $referenceEntityCode, string $recordCode): void
    {
        $matchingIdentifiers = $this->findRecord($referenceEntityCode, $recordCode);

        Assert::assertCount(1, $matchingIdentifiers, sprintf('Record not found: %s_%s', $referenceEntityCode, $recordCode));
    }

    public function assertRecordDoesNotExists(string $referenceEntityCode, string $recordCode): void
    {
        $matchingIdentifiers = $this->findRecord($referenceEntityCode, $recordCode);

        Assert::assertCount(0, $matchingIdentifiers, sprintf('This record should not exist: %s_%s', $referenceEntityCode, $recordCode));
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

    public function refreshIndex()
    {
        $this->recordClient->refreshIndex();
    }

    private function getQuery(string $referenceEntityCode, $channel, $locale, array $terms): array
    {
        $query = [
            '_source' => '_id',
            'sort' => ['updated_at' => 'desc'],
            'query' => [
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
                    'default_field' => sprintf('record_full_text_search.%s.%s', $channel, $locale),
                    'query' => sprintf('*%s*', $term),
                ],
            ];
        }

        return $query;
    }
}
