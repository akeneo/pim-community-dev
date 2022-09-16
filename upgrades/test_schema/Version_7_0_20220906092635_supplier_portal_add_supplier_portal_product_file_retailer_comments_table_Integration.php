<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

/** @group migration-supplier-portal */
final class Version_7_0_20220906092635_supplier_portal_add_supplier_portal_product_file_retailer_comments_table_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220906092635_supplier_portal_add_supplier_portal_product_file_retailer_comments_table';
    private const TABLE_NAME = 'akeneo_supplier_portal_product_file_retailer_comments';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_the_supplier_portal_product_file_retailer_comments_table_if_not_present()
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->tableExists());
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
