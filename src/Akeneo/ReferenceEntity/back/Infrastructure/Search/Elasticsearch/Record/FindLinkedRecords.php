<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindLinkedRecordsInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindLinkedRecords implements FindLinkedRecordsInterface
{
    private const INDEX_TYPE = 'pimee_reference_entity_record';

    /** @var Client */
    private $recordClient;

    public function __construct(Client $recordClient)
    {
        $this->recordClient = $recordClient;
    }

    public function __invoke(RecordIdentifier $recordIdentifier): array
    {
        $searchQuery = $this->getElasticSearchQuery($recordIdentifier);
        $matches = $this->recordClient->search(self::INDEX_TYPE, $searchQuery);

        return $this->createIdentifiers($matches);
    }

    private function getElasticSearchQuery(RecordIdentifier $recordIdentifier): array
    {
        return [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'linked_to_records' => $recordIdentifier->normalize(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return RecordIdentifier[]
     */
    private function createIdentifiers(array $matches): array
    {
        return array_map(function (array $hit) {
            return RecordIdentifier::fromString($hit['_id']);
        }, $matches['hits']['hits']);
    }
}
