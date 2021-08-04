<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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
 * This command indexes all records on temporary index (services injected by DI).
 * This command is designed to be executed once by a cron in SAAS environment (not a perfect solution but the
 * simplest one within the actual stack). This command cannot be executed several times because:
 *  - concurrent cron jobs are forbidden by configuration (the same job cannot be executed in parallel)
 *  - we update a config in DB at the end of the command, this way we know if the command was already executed
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class IndexAllRecordsOnTemporaryIndexCommand extends Command
{
    public const CONFIGURATION_CODE = 'reindex_records';
    private const REFRESH_INTERVAL_DURING_INDEXATION = '30s';

    protected static $defaultName = 'akeneo:reference-entity:reindex-records-on-fly';

    private Connection $connection;
    private ReferenceEntityRepositoryInterface $referenceEntityRepository;
    private RecordIndexerInterface $recordIndexer;
    private NativeClient $nativeClient;
    private string $recordIndexAlias;

    public function __construct(
        Connection $connection,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        RecordIndexerInterface $recordIndexer,
        ClientBuilder $clientBuilder,
        string $recordIndexAlias,
        string $hosts
    ) {
        parent::__construct(self::$defaultName);

        $this->connection = $connection;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->recordIndexer = $recordIndexer;
        $this->nativeClient = $clientBuilder->setHosts([$hosts])->build();
        $this->recordIndexAlias = $recordIndexAlias;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->migrationIsAlreadyDone()) {
            $output->writeln("<info>The migration is already done. Nothing to do.</info>");

            return 0;
        }

        if (!$io->confirm('Are you sure to continue?', true)) {
            $output->writeln("<info>You decided to abort your ElasticSearch mapping update</info>");

            return 0;
        }

        $currentIndexName = $this->getIndexNameFromIndexAlias($this->recordIndexAlias);
        $temporaryIndexAlias = $this->getTemporaryIndexAlias();
        $temporaryIndexName = $this->getIndexNameFromIndexAlias($temporaryIndexAlias);

        // Change refresh_interval to improve performance
        $oldRefreshInterval = $this->getRefreshIntervalForTemporaryIndex($temporaryIndexName);
        $this->setRefreshIntervalToTemporaryIndex($temporaryIndexName, self::REFRESH_INTERVAL_DURING_INDEXATION);

        $this->indexAllRecords($output);
        $this->setRefreshIntervalToTemporaryIndex($temporaryIndexName, $oldRefreshInterval);
        $this->switchIndexAliasToNewIndex($currentIndexName, $temporaryIndexName, $temporaryIndexAlias);
        $this->markTheMigrationAsDone();

        $output->writeln('<info>Done</info>');

        return 0;
    }

    private function indexAllRecords(OutputInterface $output): void
    {
        $referenceEntities = $this->referenceEntityRepository->all();
        foreach ($referenceEntities as $referenceEntity) {
            $output->writeln(sprintf(
                '<info>Re-index "%s" reference entity ...</info>',
                $referenceEntity->getIdentifier()->normalize()
            ));
            $this->recordIndexer->indexByReferenceEntity($referenceEntity->getIdentifier());
        }
    }

    private function setRefreshIntervalToTemporaryIndex(string $temporaryIndexName, ?string $value): void
    {
        $result = $this->nativeClient->indices()->putSettings([
            'index' => $temporaryIndexName,
            'body' => [
                'index' => [
                    'refresh_interval' => $value,
                ],
            ],
        ]);

        Assert::true($result['acknowledged'], 'The refresh interval is not set.');
        Assert::same(
            $value,
            $this->getRefreshIntervalForTemporaryIndex($temporaryIndexName),
            'The refresh interval is not set.'
        );
    }

    private function getRefreshIntervalForTemporaryIndex(string $temporaryIndexName): ?string
    {
        $results = $this->nativeClient->indices()->getSettings([
            'index' => $temporaryIndexName,
        ]);

        return $results[$temporaryIndexName]['settings']['index']['refresh_interval'] ?? null;
    }

    private function getTemporaryIndexAlias(): string
    {
        $statement = $this->connection->executeQuery(
            'SELECT `values` FROM pim_configuration WHERE code = :code',
            ['code' => self::CONFIGURATION_CODE]
        );

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $values = json_decode($result['values'], true);
        Assert::keyExists($values, 'temporary_index_alias', 'Migration does not contain the temporary index alias');

        return $values['temporary_index_alias'];
    }

    private function migrationIsAlreadyDone(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS(
                SELECT 1 FROM pim_configuration WHERE code = :code
            ) as is_existing
            SQL;
        $statement = $this->connection->executeQuery($sql, ['code' => self::CONFIGURATION_CODE]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }

    private function markTheMigrationAsDone(): void
    {
        $sql = 'DELETE FROM pim_configuration WHERE code = :code';

        $this->connection->executeQuery($sql, ['code' => self::CONFIGURATION_CODE]);
    }

    private function switchIndexAliasToNewIndex(
        string $currentIndexName,
        string $temporaryIndexName,
        string $temporaryIndexAlias
    ) {
        $result = $this->nativeClient->indices()->updateAliases([
            'body' => [
                "actions" => [
                    [
                        'add' => [
                            'index' => $temporaryIndexName,
                            'alias' => $this->recordIndexAlias,
                        ]
                    ],
                    [
                        'add' => [
                            'index' => $currentIndexName,
                            'alias' => $temporaryIndexAlias,
                        ]
                    ],
                    [
                        'remove' => [
                            'index' => $currentIndexName,
                            'alias' => $this->recordIndexAlias,
                        ]
                    ],
                    [
                        'remove' => [
                            'index' => $temporaryIndexName,
                            'alias' => $temporaryIndexAlias,
                        ]
                    ],
                ]
            ]
        ]);

        Assert::true($result['acknowledged']);
    }

    private function getIndexNameFromIndexAlias(string $indexAlias): string
    {
        $aliases = $this->nativeClient->indices()->getAlias(['name' => $indexAlias]);
        $indexNames = array_keys($aliases);
        Assert::keyExists($indexNames, 0, "No index name found from $indexAlias alias");

        return $indexNames[0];
    }
}
