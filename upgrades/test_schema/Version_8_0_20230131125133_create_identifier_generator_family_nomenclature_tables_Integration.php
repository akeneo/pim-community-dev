<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_8_0_20230131125133_create_identifier_generator_family_nomenclature_tables_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230131125133_create_identifier_generator_family_nomenclature_tables';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_the_identifier_generator_nomenclature_tables(): void
    {
        Assert::assertTrue($this->tablesExists());
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_identifier_generator_nomenclature_definition');
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_identifier_generator_family_nomenclature');
        Assert::assertFalse($this->tablesExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tablesExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function tablesExists(): bool
    {
        return $this->tableExists('pim_catalog_identifier_generator_nomenclature_definition') &&
            $this->tableExists('pim_catalog_identifier_generator_family_nomenclature');
    }

    private function tableExists($tableName): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName',
                [
                    'tableName' => $tableName,
                ]
            )->rowCount() >= 1;
    }
}
