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
final class Version_8_0_20230523094142_create_product_identifiers_table_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230523094142_create_product_identifiers_table';

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
    public function it_creates_the_product_identifiers_table(): void
    {
        if ($this->hasProductIdentifiersTable()) {
            $this->connection->executeStatement(
                <<<SQL
                    DROP TABLE pim_catalog_product_identifiers
                SQL
            );
        }
        $this->assertFalse($this->hasProductIdentifiersTable());

        $this->reExecuteMigration(self::MIGRATION_NAME);

        $this->assertTrue($this->hasProductIdentifiersTable());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_the_product_identifiers_table_already_exists(): void
    {
        $this->assertTrue($this->hasProductIdentifiersTable());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertTrue($this->hasProductIdentifiersTable());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function hasProductIdentifiersTable(): bool
    {
        return $this->connection->executeQuery(
                <<<SQL
                SHOW TABLES LIKE 'pim_catalog_product_identifiers';
            SQL,
            )->rowCount() >= 1;
    }
}
