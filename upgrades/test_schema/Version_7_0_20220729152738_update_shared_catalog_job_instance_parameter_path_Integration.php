<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;

final class Version_7_0_20220729152738_update_shared_catalog_job_instance_parameter_path_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220729152738_update_shared_catalog_job_instance_parameter_path';

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
        $this->createSharedCatalogExport();

        $this->assertTrue($this->jobContainsOldFilepath('shared_catalog_export'));
        $this->assertFalse($this->jobContainsStorage('shared_catalog_export', '/tmp/export_%job_label%_%datetime%.csv'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertFalse($this->jobContainsOldFilepath('shared_catalog_export'));
        $this->assertTrue($this->jobContainsStorage('shared_catalog_export', '/tmp/export_%job_label%_%datetime%.csv'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createSharedCatalogExport()
    {
        $this->connection->executeStatement("DELETE FROM akeneo_batch_job_instance WHERE code = 'shared_catalog_export'");

        $rawParameters = [
            'filePath' => '/tmp/export_%job_label%_%datetime%.csv',
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'user_to_notify' => NULL,
            'is_user_authenticated' => false,
            'decimalSeparator' => '.',
            'dateFormat' => 'yyyy-MM-dd',
            'with_media' => true,
            'filters' => [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => [
                            'bra_st_tropez',
                        ],
                    ],
                    [
                        'field' => 'sku',
                        'operator' => 'IN',
                        'value' => [
                            '100107253',
                        ],
                    ],
                ],
                'structure' => [
                    'scope' => 'ecommerce',
                    'locales' => [
                        'en_GB',
                    ],
                    'attributes' => [
                        'mkt_product_name',
                    ],
                ],
            ],
            'publisher' => 'gareth.bright@pzcussons.com',
            'recipients' => [
                [
                    'email' => 'an@email.com',
                ],
            ],
            'branding' => [
                'image' => null,
                'cover_image' => NULL,
                'color' => '#f9b53f',
            ],
        ];

        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	('shared_catalog_export', 'Shared Catalog Demo', 'akeneo_shared_catalog', 0, 'Akeneo Shared Catalogs', :raw_parameters, 'export');
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
            'type' => NoneStorage::TYPE
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
}
