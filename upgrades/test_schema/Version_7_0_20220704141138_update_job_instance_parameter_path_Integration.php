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

final class Version_7_0_20220704141138_update_job_instance_parameter_path_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220704141138_update_job_instance_parameter_path';

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

    public function test_it_changes_storage_path_for_import_job_instance(): void
    {
        $this->createProductImport();

        $this->assertTrue($this->jobContainsOldFilepath('xlsx_product_import'));
        $this->assertFalse($this->jobContainsStorage('xlsx_product_import', '/tmp/product.xlsx'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->jobContainsOldFilepath('xlsx_product_import'));
        $this->assertTrue($this->jobContainsStorage('xlsx_product_import', '/tmp/product.xlsx'));
    }

    public function test_it_changes_storage_path_for_export_job_instance(): void
    {
        $this->createProductExport();

        $this->assertTrue($this->jobContainsOldFilepath('csv_product_export'));
        $this->assertFalse($this->jobContainsStorage('csv_product_export', '/tmp/export_%job_label%_%datetime%.csv'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->jobContainsOldFilepath('csv_product_export'));
        $this->assertTrue($this->jobContainsStorage('csv_product_export', '/tmp/export_%job_label%_%datetime%.csv'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProductImport()
    {
        $rawParameters = [
            "filePath" => "/tmp/product.xlsx",
            "withHeader" => true,
            "uploadAllowed" => true,
            "invalid_items_file_format" => "xlsx",
            "user_to_notify" => null,
            "is_user_authenticated" => false,
            "decimalSeparator" => ".",
            "dateFormat" => "yyyy-MM-dd",
            "enabled" => true,
            "categoriesColumn" => "categories",
            "familyColumn" => "family",
            "groupsColumn" => "groups",
            "enabledComparison" => true,
            "realTimeVersioning" => true,
            "convertVariantToSimple" => false,
        ];

        $this->connection->executeStatement("DELETE FROM akeneo_batch_job_instance WHERE code = 'xlsx_product_import'");

        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	('xlsx_product_import', 'Demo XLSX product import', 'xlsx_product_import', 0, 'Akeneo XLSX Connector', :raw_parameters, 'import');
SQL;

        $this->connection->executeStatement($sql, [
            'raw_parameters' => serialize($rawParameters)
        ]);
    }

    private function createProductExport()
    {
        $rawParameters = [
            'filePath' => '/tmp/export_%job_label%_%datetime%.csv',
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'user_to_notify' => null,
            'is_user_authenticated' => false,
            'decimalSeparator' => '.',
            'dateFormat' => 'yyyy-MM-dd',
            'with_media' => true,
            'with_label' => false,
            'header_with_label' => false,
            'file_locale' => null,
            'filters' =>
                [
                    'data' =>
                        [
                            [
                                'field' => 'enabled',
                                'operator' => '=',
                                'value' => true,
                            ],
                            [
                                'field' => 'categories',
                                'operator' => 'IN CHILDREN',
                                'value' =>
                                    [
                                        'master',
                                    ],
                            ],
                            [
                                'field' => 'completeness',
                                'operator' => '>=',
                                'value' => 100,
                                'context' =>
                                    [
                                        'locales' =>
                                            [
                                                'fr_FR',
                                                'en_US',
                                                'de_DE',
                                            ],
                                    ],
                            ],
                        ],
                    'structure' =>
                        [
                            'scope' => 'ecommerce',
                            'locales' =>
                                [
                                    'fr_FR',
                                    'en_US',
                                    'de_DE',
                                ],
                        ],
                ],
        ];

        $this->connection->executeStatement("DELETE FROM akeneo_batch_job_instance WHERE code = 'csv_product_export'");

        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	('csv_product_export', 'Demo CSV product export', 'csv_product_export', 0, 'Akeneo CSV Connector', :raw_parameters, 'export');
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
