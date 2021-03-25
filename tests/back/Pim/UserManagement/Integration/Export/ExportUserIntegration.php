<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\UserManagement\Integration\Export;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use PHPUnit\Framework\Assert;

class ExportUserIntegration extends TestCase
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
     */
    public function it_exports_users_in_csv_with_enterprise_properties(): void
    {
        $expectedCsv = <<<CSV
username;email;avatar;catalog_default_locale;catalog_default_scope;default_category_tree;default_product_grid_view;enabled;first_name;groups;last_name;middle_name;name_prefix;name_suffix;phone;product_grid_filters;proposals_state_notifications;proposals_to_review_notification;roles;timezone;user_default_locale
admin;admin@example.com;;en_US;ecommerce;master;;1;John;"IT support,All";Doe;;;;;;1;1;ROLE_ADMINISTRATOR;UTC;en_US
julia;julia@example.com;;en_US;ecommerce;master;;1;Julia;Manager,All;Stark;;;;;;1;1;ROLE_CATALOG_MANAGER;UTC;en_US
mary;mary@example.com;;en_US;ecommerce;master;;1;Mary;Redactor,All;Smith;;;;;;1;1;ROLE_USER;UTC;en_US
kevin;kevin@example.com;;en_US;ecommerce;master;;1;Kevin;Redactor,All;Michel;;;;;;1;1;ROLE_TRAINEE;UTC;en_US

CSV;
        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, []);

        Assert::assertSame($expectedCsv, $csv);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
