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
final class Version_8_0_20230412073848_add_main_attribute_column_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230412073848_add_main_attribute_column';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_does_nothing_if_the_column_already_exists(): void
    {
        Assert::assertTrue($this->mainIdentifierColumnExists());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->mainIdentifierColumnExists());
    }

    public function test_it_adds_the_column(): void
    {
        if($this->mainIdentifierColumnExists()) {
            $this->connection->executeStatement(
                <<<SQL
            ALTER TABLE pim_catalog_attribute DROP COLUMN main_identifier
            SQL
            );
        }

        Assert::assertFalse($this->mainIdentifierColumnExists());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->mainIdentifierColumnExists());
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
}
