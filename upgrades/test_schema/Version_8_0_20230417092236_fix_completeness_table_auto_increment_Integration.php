<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230417092236_fix_completeness_table_auto_increment_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230417092236_fix_completeness_table_auto_increment';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_sets_the_column_as_auto_incremental(): void
    {
        $created = $this->createTableIfNotExist();
        $this->connection->executeStatement(
            <<<SQL
            ALTER TABLE pim_catalog_completeness MODIFY COLUMN id bigint NOT NULL;
            SQL
        );
        Assert::assertFalse($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        if ($created) {
            $this->dropTable();
        }
    }

    public function test_it_does_nothing_if_the_column_is_auto_incremental(): void
    {
        $created = $this->createTableIfNotExist();
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        if ($created) {
            $this->dropTable();
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function isColumnAutoIncremental(string $tableName, string $columnName): bool
    {
        $sql = <<<SQL
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = :schema
  AND TABLE_NAME = :tableName
  AND COLUMN_NAME = :columnName
  AND EXTRA like '%auto_increment%'
SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $this->connection->getDatabase(),
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return \intval($result) > 0;
    }

    private function tableExists(string $tableName): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                [
                    'tableName' => $tableName,
                ]
            )->rowCount() >= 1;
    }

    private function createTableIfNotExist(): bool
    {
        $result = !$this->tableExists('pim_catalog_completeness');

        $completenessTableSql =
            <<<SQL
CREATE TABLE IF NOT EXISTS `pim_catalog_completeness` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `locale_id` int(11) NOT NULL,
    `channel_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `ratio` int(11) NOT NULL,
    `missing_count` int(11) NOT NULL,
    `required_count` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `searchunique_idx` (`channel_id`,`locale_id`,`product_id`),
    KEY `IDX_113BA854E559DFD1` (`locale_id`),
    KEY `IDX_113BA85472F5A1AA` (`channel_id`),
    KEY `IDX_113BA8544584665A` (`product_id`),
    KEY `ratio_idx` (`ratio`),
    CONSTRAINT `FK_113BA8544584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
);
SQL;

        $this->connection->executeQuery($completenessTableSql);

        return $result;
    }

    private function dropTable(): void
    {
        $this->connection->executeQuery(<<<SQL
DROP TABLE `pim_catalog_completeness`
SQL);
    }
}
