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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordItemsForReferenceEntity implements FindRecordsForQueryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var Client */
    private $recordClient;

    /**
     * @param Connection $sqlConnection
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection, Client $recordClient)
    {
        $this->sqlConnection = $sqlConnection;
        $this->recordClient = $recordClient;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ReferenceEntityIdentifier $identifier, array $userQuery): array
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($userQuery);
        $matches = $this->recordClient->search(RecordIndexer::INDEX_TYPE, $elasticSearchQuery);
        $identifiers = array_map(function (array $hit) {
            return $hit['_id'];
        }, $matches['hits']['hits']);

        $recordItems = $this->fetchRecords($identifiers);

        return ['items' => $recordItems, 'total' => $matches['hits']['total']];
    }

    /**
     * @return string[]
     */
    private function fetchRecords(array $identifiers): array
    {
        $sqlQuery = <<<SQL
        SELECT ee.identifier, ee.reference_entity_identifier, ee.code, ee.labels, fi.image, ee.value_collection
        FROM akeneo_reference_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE identifier IN (:identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery($sqlQuery, [
            'identifiers' => $identifiers
        ], ['identifiers' => Connection::PARAM_STR_ARRAY]);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $recordItems = [];
        foreach ($results as $result) {
            $image = null !== $result['image'] ? json_decode($result['image'], true) : null;
            $recordItems[] = $this->hydrateRecordItem(
                $result['identifier'],
                $result['reference_entity_identifier'],
                $result['code'],
                $image,
                $result['labels'],
                json_decode($result['value_collection'], true)
            );
        }

        return $recordItems;
    }

    private function getElasticSearchQuery(array $userQuery) {
        $searchFilter = current(array_filter($userQuery['filters'], function ($filter) {
            return $filter['field'] === 'search';
        }));

        if (false === $searchFilter) {
            throw new \InvalidArgumentException('The query need to contains a filter on the search field');
        }

        $referenceEntityCode = current(array_filter($userQuery['filters'], function ($filter) {
            return $filter['field'] === 'reference_entity';
        }));

        if (false === $referenceEntityCode) {
            throw new \InvalidArgumentException('The query need to contains a filter on the reference_entity field');
        }

        $query = [
            '_source' => '_id',
            'from' => $userQuery['limit'] * $userQuery['page'],
            'size' => $userQuery['limit'],
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
                            'default_field' => sprintf('record_list_search.%s.%s', $userQuery['channel'], $userQuery['locale']),
                            'query'         => sprintf('*%s*', $term),
                        ],
                    ];
            }
        }

        return $query;
    }

    private function hydrateRecordItem(
        string $identifier,
        string $referenceEntityIdentifier,
        string $code,
        ?array $image,
        string $normalizedLabels,
        array $values
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($referenceEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);

        $recordImage = Image::createEmpty();

        if (null !== $image) {
            $imageKey = Type::getType(Type::STRING)
                ->convertToPHPValue($image['file_key'], $platform);
            $imageFilename = Type::getType(Type::STRING)
                ->convertToPHPValue($image['original_filename'], $platform);
            $file = new FileInfo();
            $file->setKey($imageKey);
            $file->setOriginalFilename($imageFilename);
            $recordImage = Image::fromFileInfo($file);
        }

        $recordItem = new RecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $labels;
        $recordItem->image = $recordImage;
        $recordItem->values = $values;

        return $recordItem;
    }
}
