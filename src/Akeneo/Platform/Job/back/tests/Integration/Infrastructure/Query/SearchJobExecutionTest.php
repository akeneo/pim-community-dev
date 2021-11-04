<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use \Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

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
                'another_product_import',
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
                'another_product_import',
                'import',
                new \DateTime('2020-01-02T00:00:00+00:00'),
                null,
                'STARTED',
                0,
                2,
                1,
                3
            ),
        ];

        $this->assertEquals($expectedJobExecutions, $this->getQuery()->search($query));

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->page = 2;

        $expectedJobExecutions = [
            new JobExecutionRow(
                $this->jobExecutionIds[0],
                'another_product_import',
                'import',
                new \DateTime('2020-01-01T00:00:00+00:00'),
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

        $this->assertEquals(3, $this->getQuery()->count($query));
    }

    private function loadFixtures()
    {
        $jobInstanceId = $this->fixturesLoader->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'another_product_import',
            'type' => 'import',
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $jobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'status' => 1,
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $jobInstanceId,
            'start_time' => '2020-01-02T01:00:00+01:00',
            'status' => 3,
        ]);

        $this->jobExecutionIds[] = $this->fixturesLoader->createJobExecution([
            'job_instance_id' => $jobInstanceId,
            'start_time' => null,
            'status' => 2,
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
