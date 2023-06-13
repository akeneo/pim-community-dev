<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Export;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;

final class ExportUserIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_user_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_user_export';

    private JobLauncher $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::CSV_EXPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => self::CSV_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:0:{}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => self::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:0:{}',
            ]
        );
    }

    /**
     * @test
     * @group ce
     */
    public function it_exports_users_in_csv(): void
    {
        $expectedCsv = <<<CSV
username;email;avatar;catalog_default_locale;catalog_default_scope;date_account_created;date_account_last_updated;default_category_tree;default_product_grid_view;enabled;first_name;groups;last_logged_in;last_name;login_count;middle_name;name_prefix;name_suffix;phone;product_grid_filters;roles;timezone;user_default_locale
admin;admin@example.com;;en_US;ecommerce;2023-06-13T08:09:41+02:00;2023-06-13T08:09:41+02:00;master;;1;John;"IT support";;Doe;0;;;;;;ROLE_ADMINISTRATOR;UTC;en_US
julia;julia@example.com;;en_US;ecommerce;2023-06-13T08:09:41+02:00;2023-06-13T08:09:41+02:00;master;;1;Julia;Manager;;Stark;0;;;;;;ROLE_CATALOG_MANAGER;UTC;en_US
mary;mary@example.com;;en_US;ecommerce;2023-06-13T08:09:41+02:00;2023-06-13T08:09:41+02:00;master;;1;Mary;Redactor;;Smith;0;;;;;;ROLE_USER;UTC;en_US
kevin;kevin@example.com;;en_US;ecommerce;2023-06-13T08:09:41+02:00;2023-06-13T08:09:41+02:00;master;;1;Kevin;Redactor;;Michel;0;;;;;;ROLE_TRAINEE;UTC;en_US

CSV;
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, []);

        self::assertSame($expectedCsv, $csv);
    }

    /** @test */
    public function it_exports_users_in_xlsx(): void
    {
        $xlsx = $this->jobLauncher->launchExport(self::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');
        self::assertNotEmpty($xlsx);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
