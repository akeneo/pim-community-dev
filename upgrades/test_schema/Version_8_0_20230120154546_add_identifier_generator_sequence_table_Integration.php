<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_8_0_20230120154546_add_identifier_generator_sequence_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230120154546_add_identifier_generator_sequence_table';
    private const TABLE_NAME = 'pim_catalog_identifier_generator_sequence';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_the_identifier_generator_sequence_table_if_not_present(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_identifier_generator_sequence');
        Assert::assertFalse($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    /** @test */
    public function it_does_not_fail_if_the_identifier_generator_sequence_table_if_already_created(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
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
}
