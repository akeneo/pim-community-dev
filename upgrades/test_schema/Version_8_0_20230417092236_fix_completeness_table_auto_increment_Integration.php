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
        $this->connection->executeStatement(
            <<<SQL
            ALTER TABLE pim_catalog_completeness MODIFY COLUMN id bigint NOT NULL;
            SQL
        );
        Assert::assertFalse($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
    }

    public function test_it_does_nothing_if_the_column_is_auto_incremental(): void
    {
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
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
}
