<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;

class Version_7_0_20221020120000_update_job_instance_parameter_add_login_type_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221020120000_update_job_instance_parameter_add_login_type';

    private Connection $connection;
    private JobInstanceRepository $jobInstanceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    public function test_it_is_idempotent(): void
    {
        $this->createJob('a_csv_export');
        $this->createJob('another_csv_export');

        $this->assertFalse($this->jobIsMigrated('a_csv_export'));
        $this->assertFalse($this->jobIsMigrated('another_csv_export'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->jobIsMigrated('a_csv_export'));
        $this->assertTrue($this->jobIsMigrated('another_csv_export'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createJob(string $jobCode): void
    {
        $rawParameters = [
            'storage' => [
                'type' => 'sftp',
                'file_path' => '/tmp/product.xlsx',
                'host' => 'sftp.akeneo.com',
                'port' => 22,
                'username' => 'admin',
                'password' => 'admin',
            ],
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'users_to_notify' => [],
            'is_user_authenticated' => false,
            'decimalSeparator' => '.',
            'dateFormat' => 'yyyy-MM-dd',
            'with_media' => true,
            'with_label' => false,
            'header_with_label' => false,
            'file_locale' => NULL,
            'filters' => [],
        ];

        $this->connection->executeStatement(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :job_code',
            [
                'job_code' => $jobCode,
            ]
        );

        $sql = <<<SQL
            INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES
                (:job_code, :job_code, :job_code, 0, 'Akeneo CSV Connector', :raw_parameters, 'export');
        SQL;

        $this->connection->executeStatement($sql, [
            'job_code' => $jobCode,
            'raw_parameters' => serialize($rawParameters),
        ]);
    }

    private function jobIsMigrated(string $jobCode): bool
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByCode($jobCode);
        $rawParameters = $jobInstance->getRawParameters();

        return isset($rawParameters['storage']['login_type']);
    }
}
