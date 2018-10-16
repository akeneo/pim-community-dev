<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexer implements RecordIndexerInterface
{
    private const INDEX_TYPE = 'pimee_reference_entity_record';
    private const KEY_AS_ID = 'identifier';

    /** @var Client */
    private $recordClient;

    /** @var RecordNormalizerInterface */
    private $normalizer;

    public function __construct(
        Client $recordClient,
        RecordNormalizerInterface $normalizer
    ) {
        $this->recordClient = $recordClient;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function bulkindex(array $recordIdentifiers): void
    {
        if (empty($recordIdentifiers)) {
            return;
        }

        $normalizedrecords = array_map(function (RecordIdentifier $recordIdentifier) {
            return $this->normalizer->normalizeRecord($recordIdentifier);
        }, $recordIdentifiers);

        $this->recordClient->bulkindexes(self::INDEX_TYPE, $normalizedrecords, self::KEY_AS_ID, refresh::disable());
    }

    public function indexByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $normalizedSearchableRecords = $this->normalizer->normalizeRecordsByReferenceEntity($referenceEntityIdentifier);
        $toIndex = [];
        foreach ($normalizedSearchableRecords as $normalizedSearchableRecord) {
            $toIndex[] = $normalizedSearchableRecord;

            if (\count($toIndex) % 100 === 0) {
                $this->recordClient->bulkindexes(self::INDEX_TYPE, $toIndex, self::KEY_AS_ID, refresh::disable());
                $toIndex = [];
            }

        }

        if (!empty($toIndex)) {
            $this->recordClient->bulkindexes(self::INDEX_TYPE, $toIndex, self::KEY_AS_ID, refresh::disable());
        }
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
