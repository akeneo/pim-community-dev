<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_5_0_20200826153300_add_primary_key_connection_integration extends TestCase
{
    use ExecuteMigrationTrait;

    /** @var Connection */
    private $dbalConnection;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    private const MIGRATION_LABEL = '_5_0_20200826153300_add_primary_key_connection';

    public function test_it_is_idempotent(): void
    {
        $this->dbalConnection->executeQuery('DROP TABLE IF EXISTS akeneo_connectivity_connection');
        $this->reExecuteMigration('_4_0_20191014111427_create_connection_table');
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $indexes = $this->schemaManager->listTableIndexes('akeneo_connectivity_connection');
        $this->assertArrayHasKey('primary', $indexes);
        $pkIndex = $indexes['primary'];
        Assert::assertTrue($pkIndex->isPrimary());
        Assert::assertEquals(['code'], $pkIndex->getColumns());

        Assert::assertTrue($this->schemaManager->tablesExist('akeneo_connectivity_connection'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->schemaManager  = $this->dbalConnection->getSchemaManager();
    }
}
