<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Repository\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\DateSanitizer;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;

class JobExecutionRepositoryIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    public function testGetLastOperationsData()
    {
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_export', 'admin');

        $result = $this->getJobExecutionRepository()->getLastOperationsData([], 'admin');
        $this->assertNotNull($result[0]['id']);

        $result[0]['date'] = DateSanitizer::sanitize($result[0]['date']->format('c'));
        unset($result[0]['id']);

        $expectedResult = [
            [
                'date'         => 'this is a date formatted to ISO-8601',
                'type'         => 'export',
                'label'        => 'CSV product export',
                'status'       => 1,
                'warningCount' => '0',
            ],
        ];

        $this->assertCount(1, $result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return JobExecutionRepository
     */
    protected function getJobExecutionRepository(): JobExecutionRepository
    {
        return $this->get('pim_enrich.repository.job_execution');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
