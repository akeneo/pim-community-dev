<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_7_0_20221020120000_update_job_instance_parameter_add_login_type_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221020120000_update_job_instance_parameter_add_login_type';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_add_login_type_to_raw_parameters(): void
    {
        $this->createJob('a_csv_export', 'export');
        $this->createJob('not_a_csv_export', 'mass_edit');

        $this->assertFalse($this->isJobParametersHasLoginType('a_csv_export'));
        $this->assertFalse($this->isJobParametersHasLoginType('not_a_csv_export'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->isJobParametersHasLoginType('a_csv_export'));
        $this->assertFalse($this->isJobParametersHasLoginType('not_a_csv_export'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createJob(string $jobCode, string $type): void
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
                (:job_code, :job_code, :job_code, 0, 'Akeneo CSV Connector', :raw_parameters, :type);
        SQL;

        $this->connection->executeStatement($sql, [
            'job_code' => $jobCode,
            'raw_parameters' => serialize($rawParameters),
            'type' => $type
        ]);
    }

    private function isJobParametersHasLoginType(string $jobCode): bool
    {
        $sql = <<<SQL
            SELECT raw_parameters FROM akeneo_batch_job_instance WHERE code =  :jobCode;
        SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['jobCode' => $jobCode]
        )->fetchAssociative();
        $rawParameters = unserialize($result['raw_parameters']);

        return isset($rawParameters['storage']['login_type']);
    }
}
