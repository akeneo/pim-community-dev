<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Export;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;

final class ExportUserRoleIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_user_role_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_user_role_export';

    private JobLauncher $jobLauncher;

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

    /**
     * @test
     * @group ce
     */
    public function it_exports_user_roles_in_csv(): void
    {
        $csv = trim($this->jobLauncher->launchExport(static::CSV_EXPORT_JOB_CODE, null, []));
        $lines = explode(PHP_EOL, $csv);
        self::assertCount(4, $lines);
        self::assertSame('label;permissions', $lines[0]);
        self::assertStringContainsString('Administrator', $lines[1]);
        self::assertStringContainsString('Catalog manager', $lines[2]);
        self::assertStringContainsString('User', $lines[3]);

        $permissionsForAdministrator = $this->getPermissions($lines[1]);
        self::assertContains('action:oro_config_system', $permissionsForAdministrator);
        self::assertContains('action:pim_enrich_product_create', $permissionsForAdministrator);
    }

    /** @test */
    public function it_exports_user_roles_in_xlsx(): void
    {
        $xlsx = $this->jobLauncher->launchExport(static::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');
        self::assertNotEmpty($xlsx);
    }

    private function getPermissions(string $csvLine): array
    {
        $cells = explode(';', $csvLine);
        $stringPermissions = $cells[1] ?? '';

        return explode(',', $stringPermissions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
