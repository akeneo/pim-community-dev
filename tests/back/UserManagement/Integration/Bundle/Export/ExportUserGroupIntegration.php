<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Export;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

final class ExportUserGroupIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_user_group_export';

    protected JobLauncher $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
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
        $xlsx = $this->jobLauncher->launchExport('xlsx_user_group_export', null, [], 'xlsx');
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
