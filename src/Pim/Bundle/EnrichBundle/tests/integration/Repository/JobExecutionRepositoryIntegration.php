<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\tests\integration\Repository;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\DateSanitizer;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository;

class JobExecutionRepositoryIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    public function testGetLastOperationsData()
    {
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_export', 'admin');

        $result = $this->getJobExecutionRepository()->getLastOperationsData([], 'admin');
        $result[0]['date'] = DateSanitizer::sanitize($result[0]['date']->format('c'));

        $expectedResult = [
            [
                'id'           => 20,
                'date'         => 'this is a date formatted to ISO-8601',
                'type'         => 'export',
                'label'        => 'CSV product export',
                'status'       => 1,
                'warningCount' => "0",
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return JobExecutionRepository
     */
    protected function getJobExecutionRepository(): JobExecutionRepository
    {
        return $this->get('pim_enrich.repository.job_execution');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()]
        );
    }
}
