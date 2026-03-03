<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230720113636_drop_product_completeness_table_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230720113636_drop_product_completeness_table';
    private const TABLE_NAME = 'pim_catalog_completeness';

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
        if (!$this->tableExists()) {
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
        if ($this->tableExists()) {
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
            CREATE TABLE IF NOT EXISTS `pim_catalog_completeness` (
                `id` bigint NOT NULL AUTO_INCREMENT,
                `locale_id` int(11) NOT NULL,
                `channel_id` int(11) NOT NULL,
                `product_uuid` BINARY(16) NOT NULL,
                `missing_count` int(11) NOT NULL,
                `required_count` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `channel_locale_product_unique_idx` (`channel_id`,`locale_id`,`product_uuid`),
                KEY `IDX_113BA854E559DFD1` (`locale_id`),
                KEY `IDX_113BA85472F5A1AA` (`channel_id`),
                KEY `product_uuid` (`product_uuid`),
                CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
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
