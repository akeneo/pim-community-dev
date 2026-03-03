<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230615085833_drop_product_identifiers_table_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230615085833_drop_product_identifiers_table';
    private const TABLE_NAME = 'pim_catalog_product_identifiers';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /**
     * @test
     */
    public function it_removes_the_table(): void
    {
        if(!$this->tableExists()) {
            $this->createTable();
        }

        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertFalse($this->tableExists());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_table_does_not_exist(): void
    {
        if($this->tableExists()) {
            $this->dropTable();
        }

        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertFalse($this->tableExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function tableExists(): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                [
                    'tableName' => self::TABLE_NAME,
                ]
            )->rowCount() >= 1;
    }

    private function createTable(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_product_identifiers(
                product_uuid BINARY(16) NOT NULL PRIMARY KEY,
                identifiers JSON NOT NULL DEFAULT (JSON_ARRAY()),
                CONSTRAINT pim_catalog_product_identifiers_pim_catalog_product_uuid_fk
                    FOREIGN KEY (product_uuid) REFERENCES `pim_catalog_product` (uuid)
                        ON DELETE CASCADE,
                INDEX idx_identifiers ( (CAST(identifiers AS CHAR(511) ARRAY)) )
            )
        SQL
        );
    }

    private function dropTable(): void
    {
        $this->connection->executeStatement(<<<SQL
                DROP TABLE IF EXISTS :tableName
            SQL,
            ['tableName' => self::TABLE_NAME]
        );
    }

}
