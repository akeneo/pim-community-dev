<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Symfony\Command\Installer;

use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesInstaller;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
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
    private $assetClient;

    private const ASSET_INDEX = 'pimee_asset_family_asset';

    private const TOTAL_ASSETS = 6;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixturesInstaller = $this->get('akeneo_assetmanager.command.installer.fixtures_installer');
        $this->sqlConnection = $this->get('database_connection');
        $this->assetClient =  $this->get('akeneo_assetmanager.client.asset');
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
DROP TABLE akeneo_asset_manager_attribute;
DROP TABLE akeneo_asset_manager_asset;
DROP TABLE akeneo_asset_manager_asset_family;
DROP TABLE akeneo_asset_manager_asset_family_permissions;
SET FOREIGN_KEY_CHECKS = 1;
SQL;
        $this->sqlConnection->executeUpdate($dropSchema);
        $this->assetClient->resetIndex();
    }

    private function assertSchemaCreated(): void
    {
        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = $this->sqlConnection->getSchemaManager();
        $expectedTables = [
            'akeneo_asset_manager_asset',
            'akeneo_asset_manager_attribute',
            'akeneo_asset_manager_asset_family',
            'akeneo_asset_manager_asset_family_permissions',
        ];
        Assert::assertTrue($schemaManager->tablesExist($expectedTables));
    }

    private function assertFixturesPersisted(): void
    {
        Assert::assertEquals(3, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family;')->rowCount());
        Assert::assertEquals(19, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_attribute')->rowCount());
        Assert::assertEquals(self::TOTAL_ASSETS, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family_permissions')->rowCount());
    }

    private function assertFixturesNotLoaded(): void
    {
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family;')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_attribute')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family_permissions')->rowCount());
    }

    private function assertFixturesIndexed(): void
    {
        Assert::assertEquals(self::TOTAL_ASSETS, $this->numbersOfAssetsIndexed());
    }

    private function numbersOfAssetsIndexed(): int
    {
        $this->assetClient->refreshIndex();
        $matches = $this->assetClient->search(self::ASSET_INDEX, ['_source' => '_id' ]);

        return $matches['hits']['total'];
    }
}
