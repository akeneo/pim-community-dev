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
final class Version_8_0_20230110131126_update_target_column_for_identifier_generators_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230110131126_update_target_column_for_identifier_generators';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_does_nothing_if_the_column_already_exists(): void
    {
        Assert::assertTrue($this->columnExists('pim_catalog_identifier_generator', 'target_id'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->columnExists('pim_catalog_identifier_generator', 'target_id'));
    }

    public function test_it_executes_the_migration(): void
    {
        $this->connection->executeQuery(<<<SQL
ALTER TABLE pim_catalog_identifier_generator DROP CONSTRAINT pim_catalog_identifier_generator_ibfk_1;
ALTER TABLE pim_catalog_identifier_generator ADD COLUMN target VARCHAR(100) NOT NULL AFTER target_id;
ALTER TABLE pim_catalog_identifier_generator DROP COLUMN target_id;
INSERT INTO pim_catalog_identifier_generator (uuid, code, target, delimiter, labels, conditions, structure)
VALUES (UUID_TO_BIN('22e35c7a-f1b4-4b81-a890-16b8e68346a1'), 'mygenerator', 'sku', '', '{}', '[]', '[]');
SQL);

        Assert::assertFalse($this->columnExists('pim_catalog_identifier_generator', 'target_id'));
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->columnExists('pim_catalog_identifier_generator', 'target_id'));
        // The default value if an INT NOT NULL is 0; the migration will update it to the sku attribute id.
        Assert::assertNotEquals(0, $this->getTargetId());
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getTargetId(): int
    {
        return \intval($this->connection->fetchOne(<<<SQL
SELECT target_id FROM pim_catalog_identifier_generator LIMIT 1;
SQL));
    }
}
