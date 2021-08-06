<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexMigration;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Clock\ClockInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\GetRecordIdentifiersUpdatedAfterDatetime;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexer;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class ReindexRecordsWithoutDowntime
{
    private const REFRESH_INTERVAL_DURING_INDEXATION = '-1';

    private NativeClient $nativeClient;
    private ClockInterface $clock;
    private RecordNormalizerInterface $recordIndexationNormalizer;
    private Client $currentIndexRecordClient;
    private GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime;
    private string $recordIndexAlias;
    private int $batchSize;
    private LoggerInterface $logger;

    public function __construct(
        ClientBuilder $clientBuilder,
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        Client $currentIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime,
        LoggerInterface $logger,
        string $recordIndexAlias,
        array $elasticsearchHosts,
        int $batchSize
    ) {
        $this->nativeClient = $clientBuilder->setHosts($elasticsearchHosts)->build();
        $this->clock = $clock;
        $this->recordIndexationNormalizer = $recordIndexationNormalizer;
        $this->currentIndexRecordClient = $currentIndexRecordClient;
        $this->getRecordIdentifiersUpdatedAfterDatetime = $getRecordIdentifiersUpdatedAfterDatetime;
        $this->logger = $logger;
        $this->recordIndexAlias = $recordIndexAlias;
        $this->batchSize = $batchSize;
    }

    public function execute(Client $migratedIndexClient, string $migratedIndexAlias, string $migratedIndexName): void
    {
        $currentIndexName = $this->getCurrentIndexName();

        // Change refresh_interval to improve performance
        $oldRefreshInterval = $this->getRefreshIntervalForIndex($migratedIndexName);
        $this->setRefreshIntervalToIndex($migratedIndexName, self::REFRESH_INTERVAL_DURING_INDEXATION);

        $lastReferenceDatetime = $this->reindexRecords(
            $this->currentIndexRecordClient,
            $migratedIndexClient
        );

        $this->setRefreshIntervalToIndex($migratedIndexName, $oldRefreshInterval);
        $migratedIndexClient->refreshIndex();
        $this->switchIndexAliasToNewIndex($currentIndexName, $migratedIndexName, $migratedIndexAlias);
        $indexedRecordsAfterSwitch = $this->reindexRecordsUpdatedAfterDatetime(
            $migratedIndexClient,
            $this->currentIndexRecordClient,
            $lastReferenceDatetime
        );

        $this->logger->info('Reindexing records after switch', [
            'count' => $indexedRecordsAfterSwitch
        ]);

        $this->removeIndex($currentIndexName);
    }

    private function reindexRecords(Client $sourceClient, Client $targetClient): \DateTimeImmutable
    {
        $lastReferenceDatetime = $this->clock->now()->setTimestamp(0);

        do {
            $nextUpdateTime = $this->clock->now()->modify('-1msec');
            $this->logger->info('Start reindexing records', [
                'date' => $lastReferenceDatetime->format(\DateTimeInterface::ISO8601)
            ]);

            $indexedRecords = $this->reindexRecordsUpdatedAfterDatetime(
                $sourceClient,
                $targetClient,
                $lastReferenceDatetime
            );

            $this->logger->info('Reindexing records done', [
                'count' => $indexedRecords
            ]);

            $lastReferenceDatetime = $nextUpdateTime;
        } while ($indexedRecords > 0);

        return $lastReferenceDatetime;
    }

    private function reindexRecordsUpdatedAfterDatetime(
        Client $sourceClient,
        Client $targetClient,
        \DateTimeImmutable $referenceDatetime
    ): int {
        $indexedRecords = 0;
        $indexer = new RecordIndexer($targetClient, $this->recordIndexationNormalizer, $this->batchSize);

        $batchedNormalizedRecordIdentifiersToIndex = $this->getRecordIdentifiersUpdatedAfterDatetime->nextBatch(
            $sourceClient,
            $referenceDatetime,
            $this->batchSize
        );

        foreach ($batchedNormalizedRecordIdentifiersToIndex as $normalizedRecordIdentifiersToIndex) {
            $indexedRecords += \count($normalizedRecordIdentifiersToIndex);
            $recordIdentifiersToIndex = \array_map(
                static fn($normalizedRecordIdentifier) => RecordIdentifier::fromString($normalizedRecordIdentifier),
                $normalizedRecordIdentifiersToIndex
            );

            $indexer->indexByRecordIdentifiers($recordIdentifiersToIndex);
        }

        return $indexedRecords;
    }

    private function setRefreshIntervalToIndex(string $indexName, ?string $value): void
    {
        $result = $this->nativeClient->indices()->putSettings([
            'index' => $indexName,
            'body' => [
                'index' => [
                    'refresh_interval' => $value,
                ],
            ],
        ]);

        Assert::true($result['acknowledged'], 'The refresh interval is not set.');
        Assert::same($value, $this->getRefreshIntervalForIndex($indexName), 'The refresh interval is not set.');
    }

    private function getRefreshIntervalForIndex(string $indexName): ?string
    {
        $results = $this->nativeClient->indices()->getSettings(['index' => $indexName]);

        return $results[$indexName]['settings']['index']['refresh_interval'] ?? null;
    }

    private function switchIndexAliasToNewIndex(string $oldIndexName, string $newIndexName, string $migratedIndexAlias): void
    {
        $result = $this->nativeClient->indices()->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => [
                            'alias' => $this->recordIndexAlias,
                            'index' => $newIndexName,
                        ],
                    ],
                    [
                        'remove' => [
                            'alias' => $this->recordIndexAlias,
                            'index' => $oldIndexName,
                        ],
                    ],
                    [
                        'add' => [
                            'alias' => $migratedIndexAlias,
                            'index' => $oldIndexName,
                        ]
                    ],
                    [
                        'remove' => [
                            'alias' => $migratedIndexAlias,
                            'index' => $newIndexName,
                        ]
                    ],
                ]
            ]
        ]);

        Assert::true($result['acknowledged'], 'Index switch is not acknowledged');

        $indexNameAfterMigration = $this->getCurrentIndexName();
        Assert::notEq($oldIndexName, $indexNameAfterMigration, 'The index name used by the alias have not changed');
    }

    private function removeIndex(string $indexName): void
    {
        $result = $this->nativeClient->indices()->delete(['index' => $indexName]);

        Assert::true($result['acknowledged'], 'Remove index is not acknowledged');
    }

    private function getCurrentIndexName(): string
    {
        $aliases = $this->nativeClient->indices()->getAlias(['name' => $this->recordIndexAlias]);
        $indexNames = \array_keys($aliases);
        Assert::keyExists($indexNames, 0, 'No index name found from $indexAlias index alias');
        Assert::count($indexNames, 1, 'Cannot migrated an index alias with more than one index');

        return $indexNames[0];
    }
}
