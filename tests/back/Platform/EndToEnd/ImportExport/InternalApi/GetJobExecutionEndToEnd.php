<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\EndToEnd\ImportExport\InternalApi;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemWriter;
use Symfony\Component\HttpFoundation\Response;

class GetJobExecutionEndToEnd extends InternalApiTestCase
{
    private Connection $sqlConnection;
    private FilesystemWriter $archivistFilesystem;

    public function testGetJobExecution()
    {
        $jobExecutionId = $this->thereIsAJobTerminated();

        $this->client->request(
            'GET',
            sprintf('/job-execution/rest/%s', $jobExecutionId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($this->getExpectedContent($jobExecutionId), json_decode($response->getContent(), true));
    }

    private function thereIsAJobTerminated(): int
    {
        $JobInstanceId = $this->sqlConnection->executeQuery('SELECT id FROM akeneo_batch_job_instance WHERE code = "csv_product_import";')->fetchOne();
        $insertJobExecution = <<<SQL
INSERT INTO `akeneo_batch_job_execution` (`job_instance_id`, `pid`, `user`, `status`, `start_time`, `end_time`, `create_time`, `updated_time`, `health_check_time`, `exit_code`, `exit_description`, `failure_exceptions`, `log_file`, `raw_parameters`)
VALUES
	(:job_instance_id, 86472, 'admin', 1, '2020-10-13 13:05:49', '2020-10-13 13:06:10', '2020-10-13 13:05:45', '2020-10-13 13:06:09', '2020-10-13 13:06:09', 'COMPLETED', '', 'a:0:{}', '/srv/pim/var/logs/batch/19/batch_b2b74e2d3bc04a1918954dae504009718a46a874.log', '{}');
SQL;
        $this->sqlConnection->executeUpdate($insertJobExecution, ['job_instance_id' => $JobInstanceId]);
        $jobExecutionId = (int)$this->sqlConnection->lastInsertId();

        $insertStepExecutions = <<<SQL
INSERT INTO `akeneo_batch_step_execution` (`job_execution_id`, `step_name`, `status`, `read_count`, `write_count`, `filter_count`, `start_time`, `end_time`, `exit_code`, `exit_description`, `terminate_only`, `failure_exceptions`, `errors`, `summary`, `tracking_data`)
VALUES
	(:job_execution_id, 'validation', 1, 0, 0, 0, '2020-10-13 13:05:50', '2020-10-13 13:05:55', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:\"charset_validator.title\";s:8:\"UTF-8 OK\";}', '{"processedItems": 0, "totalItems": 0}'),
	(:job_execution_id, 'import', 1, 0, 0, 0, '2020-10-13 13:05:55', '2020-10-13 13:06:09', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:3:{s:13:\"item_position\";i:38;s:23:\"product_skipped_no_diff\";i:37;s:4:\"skip\";i:1;}', '{"processedItems": 10, "totalItems": 100}'),
	(:job_execution_id, 'import_associations', 1, 0, 0, 0, '2020-10-13 13:06:09', '2020-10-13 13:06:10', 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:0:{}', '{"processedItems": 0, "totalItems": 0}')
;
SQL;
        $this->sqlConnection->executeUpdate($insertStepExecutions, ['job_execution_id' => $jobExecutionId]);

        $this->archivistFilesystem->write("import/csv_product_import/$jobExecutionId/log/batch_b2b74e2d3bc04a1918954dae504009718a46a874.log", 'content');

        return $jobExecutionId;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->archivistFilesystem = $this->get('oneup_flysystem.archivist_filesystem');
        $this->sqlConnection = $this->get('database_connection');
        $this->authenticate($this->getAdminUser());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getExpectedContent($jobExecutionId): array
    {
        return [
            'failures' => [],
            'stepExecutions' => [
                [
                    'label' => 'validation',
                    'job' => 'csv_product_import',
                    'status' => 'Completed',
                    'status_code' => 'COMPLETED',
                    'summary' => [
                        'File encoding:' => 'UTF-8 OK',
                    ],
                    'startedAt' => '10/13/2020 01:05 PM',
                    'endedAt' => '10/13/2020 01:05 PM',
                    'warnings' => [],
                    'errors' => [],
                    'failures' => [],
                ],
                [
                    'label' => 'import',
                    'job' => 'csv_product_import',
                    'status' => 'Completed',
                    'status_code' => 'COMPLETED',
                    'summary' => [
                        'read lines' => 38,
                        'skipped product (no differences)' => 37,
                        'skipped' => 1,
                    ],
                    'startedAt' => '10/13/2020 01:05 PM',
                    'endedAt' => '10/13/2020 01:06 PM',
                    'warnings' => [],
                    'errors' => [],
                    'failures' => [],
                ],
                [
                    'label' => 'import_associations',
                    'job' => 'csv_product_import',
                    'status' => 'Completed',
                    'status_code' => 'COMPLETED',
                    'summary' => [],
                    'startedAt' => '10/13/2020 01:06 PM',
                    'endedAt' => '10/13/2020 01:06 PM',
                    'warnings' => [],
                    'errors' => [],
                    'failures' => [],
                ],
            ],
            'isRunning' => false,
            'isStoppable' => false,
            'status' => 'Completed',
            'jobInstance' => [
                'code' => 'csv_product_import',
                'job_name' => 'csv_product_import',
                'label' => 'CSV product import',
                'connector' => 'Akeneo CSV Connector',
                'type' => 'import',
                'configuration' =>
                [
                    'storage' => [
                        'type' => 'local',
                        'file_path' => '/tmp/footwear_products.csv',
                    ],
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'escape' => '\\',
                    'withHeader' => true,
                    'uploadAllowed' => true,
                    'invalid_items_file_format' => 'csv',
                    'users_to_notify' => [],
                    'is_user_authenticated' => false,
                    'decimalSeparator' => '.',
                    'dateFormat' => 'yyyy-MM-dd',
                    'enabled' => true,
                    'categoriesColumn' => 'categories',
                    'familyColumn' => 'family',
                    'groupsColumn' => 'groups',
                    'enabledComparison' => true,
                    'realTimeVersioning' => true,
                    'convertVariantToSimple' => false,
                ],
                'automation' => null,
                'scheduled' => false,
            ],
            'tracking' => [
                'error'   => false,
                'warning' => false,
                'status'  => 'COMPLETED',
                'currentStep' => 3,
                'totalSteps' => 4,
                'steps' => [
                    [
                        'jobName' => 'csv_product_import',
                        'stepName' => 'download_files',
                        'status' => 'STARTING',
                        'isTrackable' => false,
                        'hasWarning' => false,
                        'hasError' => false,
                        'duration' => 0,
                        'processedItems' => 0,
                        'totalItems' => 0,
                    ],
                    [
                        'jobName' => 'csv_product_import',
                        'stepName' => 'validation',
                        'status' => 'COMPLETED',
                        'isTrackable' => false,
                        'hasWarning' => false,
                        'hasError' => false,
                        'duration' => 5,
                        'processedItems' => 0,
                        'totalItems' => 0,
                    ],
                    [
                        'jobName' => 'csv_product_import',
                        'stepName' => 'import',
                        'status' => 'COMPLETED',
                        'isTrackable' => true,
                        'hasWarning' => false,
                        'hasError' => false,
                        'duration' => 14,
                        'processedItems' => 10,
                        'totalItems' => 100,
                    ],
                    [
                        'jobName' => 'csv_product_import',
                        'stepName' => 'import_associations',
                        'status' => 'COMPLETED',
                        'isTrackable' => true,
                        'hasWarning' => false,
                        'hasError' => false,
                        'duration' => 1,
                        'processedItems' => 0,
                        'totalItems' => 0,
                    ],
                ],
            ],
            'meta' => [
                'logExists' => true,
                'archives' => [],
                'generateZipArchive' => false,
                'id' => (string)$jobExecutionId,
            ],
        ];
    }
}
