<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexer implements RecordIndexerInterface
{
    private const KEY_AS_ID = 'identifier';

    /** @var Client */
    private $recordClient;

    /** @var RecordNormalizerInterface */
    private $normalizer;

    /** @var int */
    private $batchSize;

    public function __construct(
        Client $recordClient,
        RecordNormalizerInterface $normalizer,
        int $batchSize
    ) {
        $this->recordClient = $recordClient;
        $this->normalizer = $normalizer;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function index(RecordIdentifier $recordIdentifier): void
    {
        $normalizedRecord = $this->normalizer->normalizeRecord($recordIdentifier);
        $this->recordClient->index($normalizedRecord['identifier'], $normalizedRecord, refresh::disable());
    }

    public function indexByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $normalizedSearchableRecords = $this->normalizer->normalizeRecordsByReferenceEntity($referenceEntityIdentifier);
        $toIndex = [];
        foreach ($normalizedSearchableRecords as $normalizedSearchableRecord) {
            $toIndex[] = $normalizedSearchableRecord;

            if (\count($toIndex) % $this->batchSize === 0) {
                $this->recordClient->bulkindexes($toIndex, self::KEY_AS_ID, refresh::disable());
                $toIndex = [];
            }
        }

        if (!empty($toIndex)) {
            $this->recordClient->bulkindexes($toIndex, self::KEY_AS_ID, refresh::disable());
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

    public function refresh(): void
    {
        $this->recordClient->refreshIndex();
    }
}
