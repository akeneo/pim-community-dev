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
    private const TOTAL_ASSETS = 28;

    private FixturesInstaller $fixturesInstaller;

    private Connection $sqlConnection;

    private Client $assetClient;

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
    public function it_loads_and_index_the_catalog(): void
    {
        $this->fixturesInstaller->createSchema();
        $this->fixturesInstaller->loadCatalog();
        $this->assertFixturesPersisted();
        $this->assertFixturesIndexed();
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
        Assert::assertEquals(6, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family;')->rowCount());
        Assert::assertEquals(20, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_attribute')->rowCount());
        Assert::assertEquals(self::TOTAL_ASSETS, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset')->rowCount());
        Assert::assertEquals(0, $this->sqlConnection->executeQuery('SELECT * FROM akeneo_asset_manager_asset_family_permissions')->rowCount());
    }

    private function assertFixturesIndexed(): void
    {
        Assert::assertEquals(self::TOTAL_ASSETS, $this->numbersOfAssetsIndexed());
    }

    private function numbersOfAssetsIndexed(): int
    {
        $this->assetClient->refreshIndex();
        $matches = $this->assetClient->search(['_source' => '_id' ]);

        return $matches['hits']['total']['value'];
    }
}
