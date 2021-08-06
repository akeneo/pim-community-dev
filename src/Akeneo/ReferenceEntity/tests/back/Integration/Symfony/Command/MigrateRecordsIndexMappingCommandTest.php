<?php

namespace Akeneo\ReferenceEntity\Integration\Symfony\Command;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Types\Types;
use Elasticsearch\Client as NativeClient;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MigrateRecordsIndexMappingCommandTest extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->removePreviousMigration();
    }

    /**
     * @test
     */
    public function it_does_nothing_when_index_does_not_need_reindex()
    {
        $this->markCurrentIndexMigrationAsDone();
        self::assertTrue($this->migrationIsDone());

        $indexNameBeforeMigration = $this->getCurrentRecordIndexName();
        $recordsInIndexBeforeMigration = $this->getRecordsCountInIndex();

        $this->launchCommand();

        $indexNameAfterMigration = $this->getCurrentRecordIndexName();
        $recordsInIndexAfterMigration = $this->getRecordsCountInIndex();

        self::assertTrue($this->migrationIsDone());
        self::assertSame($recordsInIndexBeforeMigration, $recordsInIndexAfterMigration);
        self::assertSame($indexNameBeforeMigration, $indexNameAfterMigration);
    }

    /**
     * @test
     */
    public function it_migrate_record_to_another_index()
    {
        self::assertFalse($this->migrationIsDone());

        $indexNameBeforeMigration = $this->getCurrentRecordIndexName();
        $recordsInIndexBeforeMigration = $this->getRecordsCountInIndex();

        $this->launchCommand();

        $indexNameAfterMigration = $this->getCurrentRecordIndexName();
        $recordsInIndexAfterMigration = $this->getRecordsCountInIndex();

        self::assertTrue($this->migrationIsDone());
        self::assertSame($recordsInIndexBeforeMigration, $recordsInIndexAfterMigration);
        self::assertNotSame($indexNameBeforeMigration, $indexNameAfterMigration);
    }


    private function removePreviousMigration(): void
    {
        $this->get('database_connection')->executeUpdate(
            'DELETE FROM pim_configuration WHERE code = :code',
            ['code' => $this->getCurrentIndexMappingMigrationCode()]
        );
    }

    private function migrationIsDone(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS(
                SELECT 1 
                FROM pim_configuration 
                WHERE code = :code
                AND JSON_EXTRACT(`values`, '$.status') = 'done'
            ) as is_existing
            SQL;

        $statement = $this->get('database_connection')->executeQuery(
            $sql,
            ['code' => $this->getCurrentIndexMappingMigrationCode()]
        );

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return (bool) $result['is_existing'];
    }

    private function markCurrentIndexMigrationAsDone(): void
    {
        $sql = <<<SQL
            INSERT INTO pim_configuration (`code`, `values`) 
            VALUES (:code, :values) 
            ON DUPLICATE KEY UPDATE `values`= :values;
        SQL;

        $this->get('database_connection')->executeUpdate(
            $sql,
            [
                'code' => $this->getCurrentIndexMappingMigrationCode(),
                'values' => ['status' => 'done']
            ],
            ['values' => Types::JSON]
        );
    }

    private function launchCommand(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('akeneo:reference-entity:migrate-records-index-mapping');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()], ['capture_stderr_separately' => true]);

        self::assertEquals(0, $commandTester->getStatusCode(), $commandTester->getErrorOutput());
    }

    private function getIndexName(): string
    {
        return self::$container->getParameter('record_index_name');
    }

    private function getCurrentRecordIndexName(): string
    {
        $indices = $this->getNativeClient()->indices();
        $aliases = $indices->getAlias(['name' => $this->getIndexName()]);

        return array_keys($aliases)[0];
    }

    private function getRecordsCountInIndex(): int
    {
        $response = $this->getNativeClient()->count(['index' => $this->getIndexName()]);

        return $response['count'];
    }

    private function getNativeClient(): NativeClient
    {
        $clientBuilder = $this->get('akeneo_elasticsearch.client_builder');
        $hosts = self::$container->getParameter('index_hosts');
        $hosts = is_string($hosts) ? [$hosts] : $hosts;

        return $clientBuilder->setHosts($hosts)->build();
    }

    private function getCurrentIndexMappingMigrationCode(): string
    {
        $currentIndexConfiguration = $this->get('akeneo_referenceentity.client.record.index_configuration.files')->load();
        $currentIndexConfigurationHash = \sha1(\json_encode($currentIndexConfiguration->buildAggregated()));

        return \sprintf('records_index_mapping_migration_%s', $currentIndexConfigurationHash);
    }
}
