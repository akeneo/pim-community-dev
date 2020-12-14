<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class Version_5_0_20201208132458_update_asset_export_prefix_suffix_Integration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->insertJobInstances();
    }

    public function testItAddsOption()
    {
        $this->assertArrayNotHasKey('with_prefix_suffix', $this->getRawParameters(10));
        $this->runMigration();
        $this->assertSame($this->getRawParameters(10)['with_prefix_suffix'], true);
    }

    public function testItChangesNothingIfOptionAlreadyTrue()
    {
        $this->assertSame($this->getRawParameters(20)['with_prefix_suffix'], true);
        $this->runMigration();
        $this->assertSame($this->getRawParameters(20)['with_prefix_suffix'], true);
    }

    public function testItChangesNothingIfOptionAlreadyFalse()
    {
        $this->assertSame($this->getRawParameters(30)['with_prefix_suffix'], false);
        $this->runMigration();
        $this->assertSame($this->getRawParameters(30)['with_prefix_suffix'], false);
    }

    public function testItChangesNothingIfJobIsNotAssetExport()
    {
        $this->assertArrayNotHasKey('with_prefix_suffix', $this->getRawParameters(40));
        $this->runMigration();
        $this->assertArrayNotHasKey('with_prefix_suffix', $this->getRawParameters(40));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertJobInstances()
    {
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO `akeneo_batch_job_instance` (`id`, `code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	(10, 'JobToUpdate', 'JobToUpdate', 'asset_manager_csv_asset_export', 0, 'Akeneo CSV Connector', 'a:8:{s:8:"filePath";s:38:"/tmp/export_%job_label%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:10:"with_media";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:23:"asset_family_identifier";s:8:"packshot";}', 'export'),
	(20, 'AlreadyHasTrue', 'AlreadyHasTrue', 'asset_manager_csv_asset_export', 0, 'Akeneo CSV Connector', 'a:9:{s:8:"filePath";s:38:"/tmp/export_%job_label%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:10:"with_media";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:23:"asset_family_identifier";s:8:"packshot";s:18:"with_prefix_suffix";b:1;}', 'export'),
	(30, 'AlreadyHasFalse', 'AlreadyHasFalse', 'asset_manager_xlsx_asset_export', 0, 'Akeneo CSV Connector', 'a:9:{s:8:"filePath";s:38:"/tmp/export_%job_label%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:10:"with_media";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:23:"asset_family_identifier";s:8:"packshot";s:18:"with_prefix_suffix";b:0;}', 'export'),
	(40, 'AnotherJob', 'AnotherJob', 'csv_locale_import', 0, 'Akeneo CSV Connector', 'a:0:{}', 'export')
SQL
        );
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    private function getRawParameters(int $id): array
    {
        $query = 'SELECT raw_parameters FROM akeneo_batch_job_instance WHERE id = :id';
        return unserialize($this->get('database_connection')->executeQuery($query, ['id' => $id])->fetchColumn());
    }
}
