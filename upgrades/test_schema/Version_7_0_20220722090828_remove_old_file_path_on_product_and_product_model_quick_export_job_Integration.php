<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;

final class Version_7_0_20220722090828_remove_old_file_path_on_product_and_product_model_quick_export_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220722090828_remove_old_file_path_on_product_and_product_model_quick_export_job';

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

    public function test_it_removes_the_filepath_parameter_for_csv_product_and_product_model_quick_export()
    {
        $this->createCsvProductAndProductModelQuickExport();

        $this->assertTrue($this->jobContainsOldFilepath('csv_product_quick_export'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertFalse($this->jobContainsOldFilepath('csv_product_quick_export'));
    }


    public function test_it_removes_the_filepath_parameter_for_xlsx_product_and_product_model_quick_export()
    {
        $this->createXlsxProductAndProductModelQuickExport();

        $this->assertTrue($this->jobContainsOldFilepath('xlsx_product_quick_export'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertFalse($this->jobContainsOldFilepath('xlsx_product_quick_export'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createCsvProductAndProductModelQuickExport()
    {
        $this->deleteJobInstance('csv_product_quick_export');

        $rawParameters = [
            'filePath' => '/tmp/export_%job_label%_%datetime%.csv',
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
            'filePathProduct' => '/tmp/1_products_export_%locale%_%scope%_%datetime%.csv',
            'filePathProductModel' => '/tmp/2_product_models_export_%locale%_%scope%_%datetime%.csv',
        ];

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES 
   ('csv_product_quick_export', 'CSV product quick export', 'csv_product_quick_export', 0, 'Akeneo CSV Connector', :raw_parameters, 'quick_export');
SQL;

        $this->connection->executeStatement($sql, [
            'raw_parameters' => serialize($rawParameters)
        ]);
    }

    private function createXlsxProductAndProductModelQuickExport()
    {
        $this->deleteJobInstance('xlsx_product_quick_export');

        $rawParameters = [
            'filePath' => '/tmp/export_%job_label%_%datetime%.xlsx',
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
            'filePathProduct' => '/tmp/1_products_export_%locale%_%scope%_%datetime%.xlsx',
            'filePathProductModel' => '/tmp/2_product_models_export_%locale%_%scope%_%datetime%.xlsx',
        ];

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES 
   ('xlsx_product_quick_export', 'XLSX product quick export', 'xlsx_product_quick_export', 0, 'Akeneo XLSX Connector', :raw_parameters, 'quick_export');
SQL;

        $this->connection->executeStatement($sql, [
            'raw_parameters' => serialize($rawParameters)
        ]);
    }

    private function deleteJobInstance(string $jobInstanceCode)
    {
        $this->connection->executeStatement(
            "DELETE FROM akeneo_batch_job_instance WHERE code = :job_instance_code",
            [
                'job_instance_code' => $jobInstanceCode,
            ]
        );
    }

    private function jobContainsOldFilepath(string $jobCode): bool
    {
        $this->jobInstanceRepository->clear();

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByCode($jobCode);
        $rawParameters = $jobInstance->getRawParameters();

        return isset($rawParameters['filePath']);
    }
}
