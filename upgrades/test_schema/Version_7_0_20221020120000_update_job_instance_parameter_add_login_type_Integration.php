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
        $this->createJob('a_csv_export_with_sftp_storage', 'export', 'a:13:{s:7:"storage";a:6:{s:4:"type";s:4:"sftp";s:9:"file_path";s:17:"/tmp/product.xlsx";s:4:"host";s:15:"sftp.akeneo.com";s:4:"port";i:22;s:8:"username";s:5:"admin";s:8:"password";s:5:"admin";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:0:{}}');
        $this->createJob('a_csv_export_with_none_storage', 'export', 'a:13:{s:7:"storage";a:1:{s:4:"type";s:4:"none";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:0:{}}');
        $this->createJob('not_a_csv_export', 'mass_edit', 'a:0:{}');

        $this->assertFalse($this->jobParametersHasLoginType('a_csv_export_with_sftp_storage'));
        $this->assertFalse($this->jobParametersHasLoginType('a_csv_export_with_none_storage'));
        $this->assertFalse($this->jobParametersHasLoginType('not_a_csv_export'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTrue($this->jobParametersHasLoginType('a_csv_export_with_sftp_storage'));
        $this->assertFalse($this->jobParametersHasLoginType('a_csv_export_with_none_storage'));
        $this->assertFalse($this->jobParametersHasLoginType('not_a_csv_export'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createJob(string $jobCode, string $type, string $rawParameters): void
    {
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
            'raw_parameters' => $rawParameters,
            'type' => $type
        ]);
    }

    private function jobParametersHasLoginType(string $jobCode): bool
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
