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

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\ReferenceEntity\Infrastructure\Clock\ClockInterface;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexMigration\ReindexRecordsWithoutDowntime;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
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
final class MigrateRecordsIndexMappingCommand extends Command
{
    public const CONFIGURATION_CODE = 'records_index_mapping_migration_%s';
    protected static $defaultName = 'akeneo:reference-entity:migrate-records-index-mapping';

    private Connection $connection;
    private ClockInterface $clock;
    private Client $currentIndexRecordClient;
    private ReindexRecordsWithoutDowntime $reindexRecordsWithoutDowntime;
    private string $recordIndexAlias;

    public function __construct(
        Connection $connection,
        ClockInterface $clock,
        Client $currentIndexRecordClient,
        ReindexRecordsWithoutDowntime $reindexRecordsWithoutDowntime,
        string $recordIndexAlias
    ) {
        parent::__construct(self::$defaultName);

        $this->connection = $connection;
        $this->clock = $clock;
        $this->currentIndexRecordClient = $currentIndexRecordClient;
        $this->reindexRecordsWithoutDowntime = $reindexRecordsWithoutDowntime;
        $this->recordIndexAlias = $recordIndexAlias;
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
        $migratedIndexAlias = \sprintf('%s-%s', $this->recordIndexAlias, $currentDatetime->getTimestamp());
        $migratedIndexClient = Client::duplicateClient($this->currentIndexRecordClient, $migratedIndexAlias);
        $migratedIndexName = $this->createIndex($migratedIndexClient);

        $this->createMigration($currentIndexConfiguration, $currentDatetime, $migratedIndexAlias, $migratedIndexName);
        $this->reindexRecordsWithoutDowntime->execute($migratedIndexClient, $migratedIndexAlias, $migratedIndexName);
        $this->markTheMigrationAsDone($currentIndexConfiguration);

        $output->writeln('<info>Done</info>');

        return 0;
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

    private function createIndex(Client $client): string
    {
        $indexCreationResponse = $client->createIndex();
        Assert::true($indexCreationResponse['acknowledged']);

        return $indexCreationResponse['index'];
    }
}
