<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class Version_7_0_20220927080024_add_identifier_generator_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220927080024_add_identifier_generator_table';
    private const TABLE_NAME = 'pim_catalog_identifier_generator';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_creates_the_identifier_generator_table_if_not_present(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_identifier_generator_sequence');
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_identifier_generator');
        Assert::assertFalse($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    /** @test */
    public function it_does_not_fail_if_the_identifier_generator_table_if_already_created(): void
    {
        Assert::assertTrue($this->tableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function tearDown(): void
    {
        // We need to set back the schema for next tests
        $this->clean();
        parent::tearDown();
    }

    private function clean(): void
    {
        $kernel = new \Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $consoleApp = new Application($kernel);
        $consoleApp->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '--full-database' => true,
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);

        $input = new ArrayInput([
            'command' => 'pim:installer:db',
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);
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
