<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexer implements RecordIndexerInterface
{
    public const INDEX_TYPE = 'pimee_reference_entity_record';
    private const KEY_AS_ID = 'identifier';

    /** @var Client */
    private $recordClient;

    /** @var RecordNormalizerInterface */
    private $normalizer;

    public function __construct(Client $recordClient, RecordNormalizerInterface $normalizer)
    {
        $this->recordClient = $recordClient;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function bulkIndex(array $records)
    {
        if (empty($records)) {
            return;
        }

        $normalizedRecords = array_map(function (Record $record) {
            return $this->normalizer->normalize($record);
        }, $records);

        $this->recordClient->bulkIndexes(self::INDEX_TYPE, $normalizedRecords, self::KEY_AS_ID, Refresh::disable());
    }

    /**
     * {@inheritdoc}
     */
    public function removeByReferenceEntityIdentifier(string $referenceEntityIdentifier)
    {
        $queryBody = [
            'query' => [
                'match' => ['reference_entity_code' => $referenceEntityIdentifier],
            ],
        ];

        $this->recordClient->deleteByQuery($queryBody);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRecordByReferenceEntityIdentifierAndCode(
        string $referenceEntityIdentifier,
        string $recordCode
    ) {
        $queryBody = [
            'query' => [
                'bool' => [
                    'must' =>
                        [
                            ['term' => ['reference_entity_code' => $referenceEntityIdentifier]],
                            ['term' => ['code' => $recordCode]],
                        ],
                ],
            ],
        ];

        $this->recordClient->deleteByQuery($queryBody);
    }
}
