<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstance;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQuery;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQueryPagination;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class SqlFindJobInstanceTest extends IntegrationTestCase
{
    public array $expectedJobInstances;
    private FindJobInstanceInterface $findJobInstanceQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findJobInstanceQuery = $this->get(FindJobInstanceInterface::class);
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_job_instances(): void
    {
        $query = new JobInstanceQuery();

        $expectedJobInstances = [
            new JobInstance('a_product_import', 'A product import', ['param1' => 'value1', 'param2' => 'value2']),
            new JobInstance('another_product_import', 'Another product import', ['param3' => 'value3', 'param4' => 'value4']),
            new JobInstance('a_scheduled_job', 'A scheduled job', ['param5' => 'value5', 'param6' => 'value6']),
            new JobInstance('a_product_export', 'A product export', ['param7' => 'value7', 'param8' => 'value8']),
            new JobInstance('a_quick_export', 'A quick export', ['param9' => 'value9', 'param1' => 'value1']),
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    /**
     * @test
     */
    public function it_returns_job_instances_filtered_on_job_name(): void
    {
        $query = new JobInstanceQuery();
        $query->jobNames = ['a_product_import'];

        $expectedJobInstances = [
            new JobInstance('a_product_import', 'A product import', ['param1' => 'value1', 'param2' => 'value2']),
            new JobInstance('another_product_import', 'Another product import', ['param3' => 'value3', 'param4' => 'value4']),
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    /**
     * @test
     */
    public function it_returns_searched_job_instances(): void
    {
        $query = new JobInstanceQuery();
        $query->search = 'a_product_import';

        $expectedJobInstances = [
            new JobInstance('a_product_import', 'A product import', ['param1' => 'value1', 'param2' => 'value2']),
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    /**
     * @test
     */
    public function it_returns_paginated_job_instances(): void
    {
        $query = new JobInstanceQuery();
        $queryPagination = new JobInstanceQueryPagination();

        $queryPagination->limit = 2;
        $query->pagination = $queryPagination;

        $expectedJobInstances = [
            new JobInstance('a_product_import', 'A product import', ['param1' => 'value1', 'param2' => 'value2']),
            new JobInstance('another_product_import', 'Another product import', ['param3' => 'value3', 'param4' => 'value4']),
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));

        $queryPagination->page = 2;
        $queryPagination->limit = 2;
        $query->pagination = $queryPagination;

        $expectedJobInstances = [
            new JobInstance('a_scheduled_job', 'A scheduled job', ['param5' => 'value5', 'param6' => 'value6']),
            new JobInstance('a_product_export', 'A product export', ['param7' => 'value7', 'param8' => 'value8']),
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    private function loadFixtures(): void
    {
        $this->expectedJobInstances[] = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
            'raw_parameters' => ['param1' => 'value1', 'param2' => 'value2'],
        ]);

        $this->expectedJobInstances[] = $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'a_product_import',
            'label' => 'Another product import',
            'type' => 'import',
            'raw_parameters' => ['param3' => 'value3', 'param4' => 'value4'],
        ]);

        $this->expectedJobInstances[] = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_scheduled_job',
            'job_name' => 'a_scheduled_job',
            'label' => 'A scheduled job',
            'type' => 'scheduled_job',
            'raw_parameters' => ['param5' => 'value5', 'param6' => 'value6'],
        ]);

        $this->expectedJobInstances[] = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
            'raw_parameters' => ['param7' => 'value7', 'param8' => 'value8'],
        ]);

        $this->expectedJobInstances[] = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_quick_export',
            'job_name' => 'a_quick_export',
            'label' => 'A quick export',
            'type' => 'quick_export',
            'raw_parameters' => ['param9' => 'value9', 'param1' => 'value1'],
        ]);
    }
}
