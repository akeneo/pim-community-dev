<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;

final class Version_7_0_20220720121428_update_quick_export_job_instance_parameter_path_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220720121428_update_quick_export_job_instance_parameter_path';

    private Connection $connection;
    private JobInstanceRepository $jobInstanceRepository;
    private VersionProviderInterface $versionProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
        $this->versionProvider = $this->get('pim_catalog.version_provider');
    }

    public function test_it_removes_filepath_parameter_for_a_quick_export()
    {
        $this->createCsvQuickExport();

        $this->assertTrue($this->jobContainsOldFilepath('csv_product_quick_export'));
        $this->assertFalse($this->jobContainsStorage('csv_product_quick_export', '/tmp/quick_export.csv'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->jobContainsOldFilepath('csv_product_quick_export'));
        $this->assertTrue($this->jobContainsStorage('csv_product_quick_export', '/tmp/quick_export.csv'));
    }

    public function test_it_removes_filepath_parameter_for_a_grid_context_quick_export()
    {
        $this->createXlsxGridContextQuickExport();

        $this->assertTrue($this->jobContainsOldFilepath('xlsx_product_grid_context_quick_export'));
        $this->assertFalse($this->jobContainsStorage('xlsx_product_grid_context_quick_export', '/tmp/quick_export.xlsx'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->jobContainsOldFilepath('xlsx_product_grid_context_quick_export'));
        $this->assertTrue($this->jobContainsStorage('xlsx_product_grid_context_quick_export', '/tmp/quick_export.xlsx'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createCsvQuickExport()
    {
        $rawParameters = [
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'user_to_notify' => NULL,
            'is_user_authenticated' => false,
            'filters' => NULL,
            'selected_properties' => NULL,
            'with_media' => true,
            'locale' => NULL,
            'scope' => NULL,
            'ui_locale' => NULL,
            'with_label' => false,
            'header_with_label' => false,
            'file_locale' => NULL,
            'filePath' => '/tmp/quick_export.csv',
            'filePathProduct' => '/tmp/1_products_export_%locale%_%scope%_%datetime%.csv',
            'filePathProductModel' => '/tmp/2_product_models_export_%locale%_%scope%_%datetime%.csv',
        ];

        $this->connection->executeStatement("DELETE FROM akeneo_batch_job_instance WHERE code = 'csv_product_quick_export'");

        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	('csv_product_quick_export', 'CSV product quick export', 'csv_product_quick_export', 0, 'Akeneo CSV Connector', :raw_parameters, 'quick_export');
SQL;

        $this->connection->executeStatement($sql, [
            'raw_parameters' => serialize($rawParameters)
        ]);
    }

    private function createXlsxGridContextQuickExport()
    {
        $rawParameters = array (
            'withHeader' => true,
            'linesPerFile' => 10000,
            'user_to_notify' => NULL,
            'is_user_authenticated' => false,
            'filters' => NULL,
            'selected_properties' => NULL,
            'with_media' => true,
            'locale' => NULL,
            'scope' => NULL,
            'ui_locale' => NULL,
            'with_label' => false,
            'header_with_label' => false,
            'file_locale' => NULL,
            'filePath' => '/tmp/quick_export.xlsx',
            'filePathProduct' => '/tmp/1_products_export_grid_context_%locale%_%scope%_%datetime%.xlsx',
            'filePathProductModel' => '/tmp/2_product_models_export_grid_context_%locale%_%scope%_%datetime%.xlsx',
        );

        $this->connection->executeStatement("DELETE FROM akeneo_batch_job_instance WHERE code = 'xlsx_product_grid_context_quick_export'");

        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	('xlsx_product_grid_context_quick_export', 'XLSX product quick export grid context', 'xlsx_product_grid_context_quick_export', 0, 'Akeneo XLSX Connector', :raw_parameters, 'quick_export');
SQL;

        $this->connection->executeStatement($sql, [
            'raw_parameters' => serialize($rawParameters)
        ]);
    }

    private function jobContainsStorage(string $jobCode, string $storageFilePath): bool
    {
        $this->jobInstanceRepository->clear();

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByCode($jobCode);
        $rawParameters = $jobInstance->getRawParameters();

        if (!isset($rawParameters['storage'])) return false;

        $expectedStorage = [
            'file_path' => $storageFilePath,
            'type' => $this->isSaaSVersion() ? NoneStorage::TYPE : LocalStorage::TYPE
        ];

        return $rawParameters['storage'] == $expectedStorage;
    }

    private function jobContainsOldFilepath(string $jobCode): bool
    {
        $this->jobInstanceRepository->clear();

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByCode($jobCode);
        $rawParameters = $jobInstance->getRawParameters();

        return isset($rawParameters['filePath']);
    }

    private function isSaaSVersion(): bool
    {
        return $this->versionProvider->isSaaSVersion();
    }
}
