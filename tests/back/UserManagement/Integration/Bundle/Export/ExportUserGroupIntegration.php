<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Export;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;

final class ExportUserGroupIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_user_group_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_user_group_export';

    protected JobLauncher $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => static::CSV_EXPORT_JOB_CODE,
            'label' => 'Test CSV',
            'job_name' => static::CSV_EXPORT_JOB_CODE,
            'status' => 0,
            'type' => 'export',
            'raw_parameters' => 'a:6:{s:8:"filePath";s:38:"/tmp/export_%job_label%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;}',
        ]);
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => static::XLSX_EXPORT_JOB_CODE,
            'label' => 'Test XLSX',
            'job_name' => static::XLSX_EXPORT_JOB_CODE,
            'status' => 0,
            'type' => 'export',
            'raw_parameters' => 'a:5:{s:8:"filePath";s:39:"/tmp/export_%job_label%_%datetime%.xlsx";s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;}',
        ]);
    }

    /** @test */
    public function it_exports_user_groups_in_csv(): void
    {
        $expectedCsv = <<<CSV
name
All
"IT support"
Manager
Redactor

CSV;
        $csv = $this->jobLauncher->launchExport(static::CSV_EXPORT_JOB_CODE, null, []);
        self::assertSame($expectedCsv, $csv);
    }

    /** @test */
    public function it_exports_user_groups_in_xlsx(): void
    {
        $xlsx = $this->jobLauncher->launchExport(static::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');
        self::assertNotEmpty($xlsx);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
