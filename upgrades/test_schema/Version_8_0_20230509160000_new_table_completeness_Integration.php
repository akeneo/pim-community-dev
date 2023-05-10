<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230509160000_new_table_completeness_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230509160000_new_table_completeness';

    private Connection $connection;

    /** @test */
    public function it_creates_the_new_completeness_tables(): void
    {
        Assert::assertTrue($this->tableExists('pim_catalog_product_completeness'));
        $this->connection->executeStatement('DROP TABLE IF EXISTS pim_catalog_product_completeness');
        Assert::assertFalse($this->tableExists('pim_catalog_product_completeness'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->tableExists('pim_catalog_product_completeness'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
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
