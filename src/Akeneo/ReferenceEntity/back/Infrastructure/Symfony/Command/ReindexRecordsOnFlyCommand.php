<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Clock\ClockInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\GetRecordIdentifiersUpdatedAfterDatetime;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexer;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * This command indexes all records on temporary index then change the alias used by the application to the temporary index.
 * This command is designed to be executed by a cron in SAAS environment to avoid having downtime when the index mapping changed.
 * Some index mapping update can be performed without having to reindex all records (on fly by elasticsearch), this command will reindexing anyway all records.
 *
 * This command cannot be launched in parallel because concurrent cron jobs are forbidden by configuration.
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class ReindexRecordsOnFlyCommand extends Command
{
    public const CONFIGURATION_CODE = 'reindex_records_%s';
    private const REFRESH_INTERVAL_DURING_INDEXATION = '-1';

    protected static $defaultName = 'akeneo:reference-entity:reindex-records-on-fly';

    private Connection $connection;
    private NativeClient $nativeClient;
    private ClockInterface $clock;
    private RecordNormalizerInterface $recordIndexationNormalizer;
    private Client $currentIndexRecordClient;
    private GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime;
    private string $recordIndexAlias;
    private int $batchSize;

    public function __construct(
        Connection $connection,
        ClientBuilder $clientBuilder,
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        Client $currentIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime,
        string $recordIndexAlias,
        array $elasticsearchHosts,
        int $batchSize
    ) {
        parent::__construct(self::$defaultName);

        $this->connection = $connection;
        $this->nativeClient = $clientBuilder->setHosts($elasticsearchHosts)->build();
        $this->clock = $clock;
        $this->recordIndexationNormalizer = $recordIndexationNormalizer;
        $this->currentIndexRecordClient = $currentIndexRecordClient;
        $this->getRecordIdentifiersUpdatedAfterDatetime = $getRecordIdentifiersUpdatedAfterDatetime;
        $this->recordIndexAlias = $recordIndexAlias;
        $this->batchSize = $batchSize;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $currentIndexConfigurationLoader = $this->currentIndexRecordClient->getConfigurationLoader();
        $currentIndexConfiguration = $currentIndexConfigurationLoader->load();
        if ($this->currentMappingIsUpToDate($currentIndexConfiguration)) {
            $output->writeln('<info>The index mapping is up to date. Nothing to do.</info>');

            return 0;
        }

        if (!$io->confirm('Index mapping migration is needed, are you sure to continue?', true)) {
            $output->writeln('<info>Index mapping migration is cancelled</info>');

            return 0;
        }

        $currentDatetime = $this->clock->now();
        $currentIndexName = $this->getCurrentIndexName();
        $migratedIndexAlias = \sprintf('%s-%s', $this->recordIndexAlias, $currentDatetime->getTimestamp());

        $migratedIndexClient = Client::duplicateClient($this->currentIndexRecordClient, $migratedIndexAlias);
        $migratedIndexName = $this->createIndex($migratedIndexClient);

        $this->createMigration($currentIndexConfiguration, $currentDatetime, $migratedIndexAlias, $migratedIndexName);

        // Change refresh_interval to improve performance
        $oldRefreshInterval = $this->getRefreshIntervalForIndex($migratedIndexName);
        $this->setRefreshIntervalToIndex($migratedIndexName, self::REFRESH_INTERVAL_DURING_INDEXATION);

        $lastUpdatedDatetimeIndexed = $this->reindexRecords(
            $this->currentIndexRecordClient,
            $migratedIndexClient,
            $output
        );

        $this->setRefreshIntervalToIndex($migratedIndexName, $oldRefreshInterval);
        $migratedIndexClient->refreshIndex();
        $this->switchIndexAliasToNewIndex($currentIndexName, $migratedIndexName, $migratedIndexAlias);
        $this->reindexRecordsUpdatedAfterDatetime(
            $migratedIndexClient,
            $this->currentIndexRecordClient,
            $lastUpdatedDatetimeIndexed
        );

        $this->removeIndex($currentIndexName);
        $this->markTheMigrationAsDone($currentIndexConfiguration);

        $output->writeln('<info>Done</info>');

        return 0;
    }

    private function reindexRecords(
        Client $sourceClient,
        Client $targetClient,
        OutputInterface $output
    ): \DateTimeImmutable {
        $lastUpdatedDatetimeIndexed = $this->clock->now()->setTimestamp(0);
        do {
            $nextUpdateTime = $this->clock->now()->modify('-1msec');
            $output->writeln(
                \sprintf(
                    'Index records updated after %s',
                    $lastUpdatedDatetimeIndexed->format(\DateTimeInterface::ISO8601)
                )
            );

            $indexedRecords = $this->reindexRecordsUpdatedAfterDatetime(
                $sourceClient,
                $targetClient,
                $lastUpdatedDatetimeIndexed
            );

            $output->writeln(\sprintf('Indexed records: %s', $indexedRecords));
            $lastUpdatedDatetimeIndexed = $nextUpdateTime;
        } while ($indexedRecords > 0);

        return $lastUpdatedDatetimeIndexed;
    }

    private function reindexRecordsUpdatedAfterDatetime(
        Client $sourceClient,
        Client $targetClient,
        \DateTimeImmutable $lastUpdatedDatetimeIndexed
    ): int {
        $indexedRecords = 0;
        $indexer = new RecordIndexer($targetClient, $this->recordIndexationNormalizer, $this->batchSize);

        $batchedNormalizedRecordIdentifiersToIndex = $this->getRecordIdentifiersUpdatedAfterDatetime->nextBatch(
            $sourceClient,
            $lastUpdatedDatetimeIndexed,
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

    private function createMigration(
        IndexConfiguration $currentIndexConfiguration,
        \DateTimeInterface $currentDatetime,
        string $newIndexAlias,
        string $newIndexName
    ) {
        $sql = <<<SQL
            INSERT INTO pim_configuration (`code`, `values`) 
            VALUES (:code, :values) 
            ON DUPLICATE KEY UPDATE `values`= :values;
        SQL;

        $this->connection->executeUpdate(
            $sql,
            [
                'code' => $this->getMigrationCode($currentIndexConfiguration),
                'values' => [
                    'started_at' => $currentDatetime->format('c'),
                    'new_index_alias' => $newIndexAlias,
                    'new_index_name' => $newIndexName,
                    'status' => 'started',
                ]
            ],
            ['values' => Types::JSON]
        );
    }

    private function currentMappingIsUpToDate(IndexConfiguration $currentIndexConfiguration): bool
    {
        $sql = <<<SQL
            SELECT EXISTS(
                SELECT 1 
                FROM pim_configuration 
                WHERE code = :code
                AND JSON_EXTRACT(`values`, '$.status') = 'done'
            ) as is_existing
            SQL;

        $migrationCode = $this->getMigrationCode($currentIndexConfiguration);
        $statement = $this->connection->executeQuery($sql, ['code' => $migrationCode]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }

    private function markTheMigrationAsDone(IndexConfiguration $currentIndexConfiguration): void
    {
        $sql = <<<SQL
            UPDATE pim_configuration
            SET `values` = JSON_SET(`values`, '$.status', 'done') 
            WHERE code = :code
        SQL;

        $this->connection->executeQuery($sql, ['code' => $this->getMigrationCode($currentIndexConfiguration)]);
    }

    private function getMigrationCode(IndexConfiguration $currentIndexConfiguration): string
    {
        $currentIndexConfigurationHash = \sha1(\json_encode($currentIndexConfiguration->buildAggregated()));

        return \sprintf(self::CONFIGURATION_CODE, $currentIndexConfigurationHash);
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

        Assert::true($result['acknowledged']);
    }

    private function removeIndex(string $indexName): void
    {
        $result = $this->nativeClient->indices()->delete(['index' => $indexName]);

        Assert::true($result['acknowledged']);
    }

    private function getCurrentIndexName(): string
    {
        $aliases = $this->nativeClient->indices()->getAlias(['name' => $this->recordIndexAlias]);
        $indexNames = \array_keys($aliases);
        Assert::keyExists($indexNames, 0, 'No index name found from $indexAlias index alias');
        Assert::count($indexNames, 1, 'Cannot migrated an index alias with more than one index');

        return $indexNames[0];
    }

    private function createIndex(Client $client): string
    {
        $indexCreationResponse = $client->createIndex();
        Assert::true($indexCreationResponse['acknowledged']);

        return $indexCreationResponse['index'];
    }
}
