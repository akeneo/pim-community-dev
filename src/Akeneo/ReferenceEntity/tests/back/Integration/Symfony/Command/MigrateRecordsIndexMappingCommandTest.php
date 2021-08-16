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
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Doctrine\DBAL\Types\Types;
use Elasticsearch\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MigrateRecordsIndexMappingCommandTest extends SqlIntegrationTestCase
{
    private IndexConfiguration $currentIndexConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currentIndexConfiguration = $this->get('akeneo_referenceentity.client.record.index_configuration.files')->load();
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
    public function it_migrates_records_to_another_index()
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
            'DELETE FROM pim_index_migration WHERE index_alias = :index_alias AND hash = :hash',
            ['index_alias' => 'akeneo_referenceentity_record_test', 'hash' => $this->currentIndexConfiguration->getHash()]
        );
    }

    private function migrationIsDone(): bool
    {
        return $this
            ->get('Akeneo\Tool\Component\Elasticsearch\PublicApi\Read\IndexMigrationIsDoneInterface')
            ->byIndexAliasAndHash('akeneo_referenceentity_record_test', $this->currentIndexConfiguration->getHash());
    }

    private function markCurrentIndexMigrationAsDone(): void
    {
        $sql = <<<SQL
            INSERT INTO pim_index_migration (`index_alias`, `hash`, `values`) 
            VALUES (:index_alias, :hash, :values) 
            ON DUPLICATE KEY UPDATE `values`= :values;
        SQL;

        $this->get('database_connection')->executeUpdate(
            $sql,
            [
                'index_alias' => 'akeneo_referenceentity_record_test',
                'hash' => $this->currentIndexConfiguration->getHash(),
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
        $indices = $this->getClient()->indices();
        $aliases = $indices->getAlias(['name' => $this->getIndexName()]);

        return array_keys($aliases)[0];
    }

    private function getRecordsCountInIndex(): int
    {
        $response = $this->getClient()->count(['index' => $this->getIndexName()]);

        return $response['count'];
    }

    private function getClient(): Client
    {
        $clientBuilder = $this->get('akeneo_elasticsearch.client_builder');
        $hosts = self::$container->getParameter('index_hosts');
        $hosts = is_string($hosts) ? [$hosts] : $hosts;

        return $clientBuilder->setHosts($hosts)->build();
    }
}
