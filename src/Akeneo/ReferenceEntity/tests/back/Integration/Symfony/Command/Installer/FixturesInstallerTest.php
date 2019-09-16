<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Symfony\Command\Installer;

use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\Installer\FixturesInstaller;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FixturesInstallerTest extends SqlIntegrationTestCase
{
    /** @var FixturesInstaller */
    private $fixturesInstaller;

    /** @var Connection */
    private $sqlConnection;

    /** @var Client */
    private $recordClient;

    private const TOTAL_RECORDS = 10026;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixturesInstaller = $this->get('akeneo_referenceentity.command.installer.fixtures_installer');
        $this->sqlConnection = $this->get('database_connection');
        $this->recordClient =  $this->get('akeneo_referenceentity.client.record');
        $this->resetPersistence();
    }

    /**
     * @test
     */
    public function it_creates_the_schema(): void
    {
        $this->fixturesInstaller->createSchema();
        $this->assertSchemaCreated();
    }

    /**
     * @test
     */
    public function it_loads_and_index_the_catalog_if_it_supports_it(): void
    {
        $this->fixturesInstaller->createSchema();
        $this->fixturesInstaller->loadCatalog(FixturesInstaller::ICE_CAT_DEMO_DEV_CATALOG);
        $this->assertFixturesPersisted();
        $this->assertFixturesIndexed();
    }

    /**
     * @test
     */
    public function it_does_not_load_the_catalog_if_it_does_not_support_it(): void
    {
        $this->fixturesInstaller->createSchema();
        $this->fixturesInstaller->loadCatalog('unsupported_catalog');
        $this->assertFixturesNotLoaded();
    }

    private function resetPersistence(): void
    {
        $dropSchema = <<<SQL
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE akeneo_reference_entity_attribute;
DROP TABLE akeneo_reference_entity_record;
DROP TABLE akeneo_reference_entity_reference_entity;
DROP TABLE akeneo_reference_entity_reference_entity_permissions;
SET FOREIGN_KEY_CHECKS = 1;
SQL;
        $this->sqlConnection->executeUpdate($dropSchema);
        $this->recordClient->resetIndex();
    }

    private function assertSchemaCreated(): void
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->sqlConnection->getSchemaManager();
        $expectedTables = [
            'akeneo_reference_entity_record',
            'akeneo_reference_entity_attribute',
            'akeneo_reference_entity_reference_entity',
            'akeneo_reference_entity_reference_entity_permissions',
        ];
        Assert::assertTrue($schemaManager->tablesExist($expectedTables));
    }

    private function assertFixturesPersisted(): void
    {
        Assert::assertEquals(7, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_reference_entity;')->rowCount());
        Assert::assertEquals(36, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_attribute')->rowCount());
        Assert::assertEquals(self::TOTAL_RECORDS, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_record')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_reference_entity_permissions')->rowCount());
    }

    private function assertFixturesNotLoaded(): void
    {
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_reference_entity;')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_attribute')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_record')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_reference_entity_reference_entity_permissions')->rowCount());
    }

    private function assertFixturesIndexed(): void
    {
        Assert::assertEquals(self::TOTAL_RECORDS, $this->numbersOfRecordsIndexed());
    }

    private function numbersOfRecordsIndexed(): int
    {
        $this->recordClient->refreshIndex();
        $matches = $this->recordClient->search(['_source' => '_id' ]);

        return $matches['hits']['total'];
    }
}
