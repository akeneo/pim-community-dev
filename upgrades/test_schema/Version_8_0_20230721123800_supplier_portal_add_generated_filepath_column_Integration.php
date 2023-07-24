<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/** @group migration-supplier-portal */
final class Version_8_0_20230721123800_supplier_portal_add_generated_filepath_column_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230721123800_supplier_portal_add_generated_filepath_column';
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_adds_the_generated_filename_column()
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('akeneo_supplier_portal_template_configuration');
        $this->assertArrayHasKey('generated_filepath', $tableColumns, 'The column `generated_filepath` should have been added to table `akeneo_supplier_portal_template_configuration`');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
