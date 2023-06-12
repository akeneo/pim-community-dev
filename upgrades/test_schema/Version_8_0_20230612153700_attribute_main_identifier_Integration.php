<?php

declare(strict_types=1);


namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230612153700_attribute_main_identifier_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230612153700_attribute_main_identifier';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function tearDown(): void
    {
        if (!$this->mainIdentifierColumnExists()) {
            $this->addColumnMainIdentifier();
        }
    }

    public function test_it_skips_migration_if_main_identifier_column_does_not_exist(): void
    {
        if ($this->mainIdentifierColumnExists()) {
            $this->dropColumnMainIdentifier();
        }
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertFalse($this->mainIdentifierColumnExists());
    }

    public function test_it_does_nothing_if_a_main_identifier_already_exists(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            UPDATE pim_catalog_attribute pca
            INNER JOIN
            (
                SELECT id
                FROM pim_catalog_attribute
                WHERE attribute_type = 'pim_catalog_identifier'
                ORDER BY id ASC
                LIMIT 1
            ) t ON pca.id = t.id
            SET main_identifier = true
            SQL
        );

        Assert::assertTrue($this->aMainIdentifierExists());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->aMainIdentifierExists());
    }

    public function test_it_adds_a_main_identifier_if_not_exists(): void
    {
        $this->connection->executeStatement(
            'UPDATE pim_catalog_attribute SET main_identifier = FALSE;'
        );
        Assert::assertFalse($this->aMainIdentifierExists());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->aMainIdentifierExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function mainIdentifierColumnExists(): bool
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('pim_catalog_attribute');

        return isset($columns['main_identifier']);
    }

    private function dropColumnMainIdentifier(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            ALTER TABLE pim_catalog_attribute DROP COLUMN main_identifier
            SQL
        );
    }

    private function addColumnMainIdentifier(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            ALTER TABLE pim_catalog_attribute ADD main_identifier TINYINT(1) NOT NULL DEFAULT FALSE
            SQL
        );
    }

    private function aMainIdentifierExists(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT * FROM pim_catalog_attribute
                WHERE main_identifier = true
            ) as is_existing
        SQL;

        return (bool) $this->connection->executeQuery($sql)->fetchOne();
    }
}
