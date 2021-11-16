<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use \Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;

class SearchJobExecutionTest extends IntegrationTestCase
{
    private array $jobExecutionIds;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jobExecutionIds = [];
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_paginated_job_executions(): void
    {
        $query = new SearchJobExecutionQuery();
        $query->size = 2;

        $expectedJobExecutions = [
            new JobExecutionRow(
                $this->jobExecutionIds[2],
                'A product import',
                'import',
                null,
                null,
                'STARTING',
                0,
                0,
                0,
                3,
            ),
            new JobExecutionRow(
                $this->jobExecutionIds[3],
                'A product export',
                'export',
                null,
                null,
                'STARTING',
                0,
                0,
                0,
                3,
            ),
        ];

        $this->assertEquals($expectedJobExecutions, $this->getQuery()->search($query));

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->page = 2;

        $expectedJobExecutions = [
            new JobExecutionRow(
                $this->jobExecutionIds[1],
                'A product import',
                'import',
                new \DateTimeImmutable('2020-01-02T00:00:00+00:00'),
                null,
                'STARTED',
                0,
                2,
                1,
                3
            ),
            new JobExecutionRow(
                $this->jobExecutionIds[0],
                'A product import',
                'import',
                new \DateTimeImmutable('2020-01-01T00:00:00+00:00'),
                null,
                'COMPLETED',
                4,
                0,
                3,
                3
            ),
        ];

        $this->assertEquals($expectedJobExecutions, $this->getQuery()->search($query));
    }

    /**
     * @test
     */
    public function it_returns_filtered_job_executions(): void
    {
        $query = new SearchJobExecutionQuery();
        $query->type = ['export'];
        $query->size = 10;

        $expectedJobExecutions = [
            new JobExecutionRow(
                $this->jobExecutionIds[3],
                'A product export',
                'export',
                null,
                null,
                'STARTING',
                0,
                0,
                0,
                3,
            ),
        ];

        $this->assertEquals($expectedJobExecutions, $this->getQuery()->search($query));
    }

    /**
     * @test
     */
    public function it_returns_filtered_job_executions_on_status(): void
    {
        $query = new SearchJobExecutionQuery();
        $query->status = ['STARTING'];
        $query->size = 10;

        $expectedJobExecutions = [
            new JobExecutionRow(
                $this->jobExecutionIds[2],
                'A product import',
                'import',
                null,
                null,
                'STARTING',
                0,
                0,
                0,
                3,
            ),
            new JobExecutionRow(
                $this->jobExecutionIds[3],
                'A product export',
                'export',
                null,
                null,
                'STARTING',
                0,
                0,
                0,
                3,
            ),
        ];

        $this->assertEquals($expectedJobExecutions, $this->getQuery()->search($query));
    }

    /**
     * @test
     */
    public function it_returns_filtered_job_executions_on_search(): void
    {
        $query = new SearchJobExecutionQuery();
        $query->search = 'Import product';
        $query->size = 10;

        $expectedJobExecutions = [
            new JobExecutionRow(
                $this->jobExecutionIds[2],
                'A product import',
                'import',
                null,
                null,
                'STARTING',
                0,
                0,
                0,
                3,
            ),
            new JobExecutionRow(
                $this->jobExecutionIds[1],
                'A product import',
                'import',
                new \DateTimeImmutable('2020-01-02T00:00:00+00:00'),
                null,
                'STARTED',
                0,
                2,
                1,
                3
            ),
            new JobExecutionRow(
                $this->jobExecutionIds[0],
                'A product import',
                'import',
                new \DateTimeImmutable('2020-01-01T00:00:00+00:00'),
                null,
                'COMPLETED',
                4,
                0,
                3,
                3
            ),
        ];

        $this->assertEquals($expectedJobExecutions, $this->getQuery()->search($query));
    }

    /**
     * @test
     */
    public function it_returns_job_execution_count_related_to_query()
    {
        $query = new SearchJobExecutionQuery();

        $this->assertEquals(4, $this->getQuery()->count($query));
    }

    /**
     * @test
     */
    public function it_returns_job_execution_count_filtered_by_type()
    {
        $query = new SearchJobExecutionQuery();
        $query->type = ['export'];

        $this->assertEquals(1, $this->getQuery()->count($query));
    }

    /**
     * @test
     */
    public function it_returns_job_execution_count_filtered_by_search()
    {
        $query = new SearchJobExecutionQuery();
        $query->search = 'Import product';

        $this->assertEquals(3, $this->getQuery()->count($query));
    }

    private function loadFixtures()
    {
        $aProductImportJobInstanceId = $this->fixturesLoader->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
        ]);

        $anotherProductImportJobInstanceId = $this->fixturesLoader->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'Another product import',
            'type' => 'import',
        ]);

        $aProductExportJobInstanceId = $this->fixturesLoader->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $aProductImportJobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'status' => BatchStatus::COMPLETED,
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $aProductImportJobInstanceId,
            'start_time' => '2020-01-02T01:00:00+01:00',
            'status' => BatchStatus::STARTED,
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $aProductImportJobInstanceId,
            'start_time' => null,
            'status' => BatchStatus::STARTING,
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $aProductExportJobInstanceId,
            'start_time' => null,
            'status' => BatchStatus::STARTING,
        ]);

        $this->fixturesLoader->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[0],
            'warning_count' => 2,
        ]);

        $this->fixturesLoader->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[0],
        ]);

        $this->fixturesLoader->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[0],
            'warning_count' => 2,
        ]);

        $this->fixturesLoader->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[1],
            'errors' => [
                'an_error' => 'a backtrace',
                'an_another_error' => 'an another backtrace',
            ]
        ]);
    }

    private function getQuery(): SearchJobExecutionInterface
    {
        return $this->get('Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface');
    }
}
