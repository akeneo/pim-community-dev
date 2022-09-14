<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class SearchJobExecutionTest extends IntegrationTestCase
{
    private SearchJobExecutionInterface $query;
    private array $jobExecutionIds;
    private array $stepExecutionIds;
    private array $cachedExpectedJobExecutionRows;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(SearchJobExecutionInterface::class);
        $this->jobExecutionIds = [];
        $this->stepExecutionIds = [];
        $this->cachedExpectedJobExecutionRows = [];
    }

    /**
     * @test
     */
    public function itReturnsPaginatedJobExecutions(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->size = 2;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[1]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[0]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->page = 2;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[5]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[4]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsFilteredJobExecutions(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->type = ['export'];
        $query->size = 10;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[3]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsFilteredJobExecutionsOnStatus(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->status = ['STARTING'];
        $query->size = 10;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[2]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[3]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsFilteredJobExecutionsOnSearch(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->search = 'Import product';
        $query->size = 10;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[1]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[0]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[2]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsFilteredJobExecutionsOnUser(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->user = ['peter', 'julia'];

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[1]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[0]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsJobExecutionCountRelatedToQuery()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();

        $this->assertEquals(6, $this->query->count($query));
    }

    /**
     * @test
     */
    public function itDoesNotCountNotVisibleJobExecutions()
    {
        $aVisibleJobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
        ]);

        $aNonVisibleJobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'prepare_evaluation',
            'job_name' => 'a_non_visible_job',
            'label' => 'A non visible job',
            'type' => 'data_quality_insights',
        ]);

        $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aVisibleJobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'user' => 'julia',
            'status' => Status::COMPLETED,
        ]);

        $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aNonVisibleJobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'user' => 'julia',
            'status' => Status::COMPLETED,
            'is_visible' => false,
        ]);

        $query = new SearchJobExecutionQuery();
        $this->assertEquals(1, $this->query->count($query));
    }

    /**
     * @test
     */
    public function itReturnsJobExecutionCountFilteredByType()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->type = ['export'];

        $this->assertEquals(1, $this->query->count($query));
    }

    /**
     * @test
     */
    public function itReturnsFilteredJobExecutionsOnCode(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->code = ['a_product_export'];
        $query->size = 10;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[3]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsFilteredJobExecutionsOnAutomation(): void
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->automation = true;

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[5]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsOrderedByJobNameJobExecutions()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->sortColumn = 'job_name';
        $query->sortDirection = 'ASC';

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[4]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[3]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsOrderedByTypeJobExecutions()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->sortColumn = 'type';
        $query->sortDirection = 'ASC';

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[4]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[3]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsOrderedByStartedAtJobExecutions()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->sortColumn = 'started_at';
        $query->sortDirection = 'ASC';

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[2]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[3]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsOrderedByUsernameJobExecutions()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->sortColumn = 'username';

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[1]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[0]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsOrderedByStatusJobExecutions()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->size = 2;
        $query->sortColumn = 'status';
        $query->sortDirection = 'ASC';

        $expectedJobExecutions = [
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[0]),
            $this->getExpectedJobExecutionRow($this->jobExecutionIds[5]),
        ];

        $this->assertEquals($expectedJobExecutions, $this->query->search($query));
    }

    /**
     * @test
     */
    public function itDoesNotReturnNotVisibleJobExecutions()
    {
        $aNonVisibleJobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'prepare_evaluation',
            'job_name' => 'a_non_visible_job',
            'label' => 'A non visible job',
            'type' => 'data_quality_insights',
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aNonVisibleJobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'user' => 'julia',
            'status' => Status::COMPLETED,
            'is_visible' => false,
        ]);

        $query = new SearchJobExecutionQuery();
        $this->assertEquals([], $this->query->search($query));
    }

    /**
     * @test
     */
    public function itReturnsJobExecutionCountFilteredBySearch()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->search = 'Import product';

        $this->assertEquals(3, $this->query->count($query));
    }

    /**
     * @test
     */
    public function itReturnsJobExecutionCountFilteredByJobInstanceCode()
    {
        $this->loadFixtures();

        $query = new SearchJobExecutionQuery();
        $query->code = ['a_product_export'];

        $this->assertEquals(1, $this->query->count($query));
    }

    private function loadFixtures()
    {
        $aProductImportJobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'Another product import',
            'type' => 'import',
        ]);

        $aProductExportJobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aProductImportJobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'user' => 'julia',
            'status' => Status::COMPLETED,
            'is_stoppable' => false,
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aProductImportJobInstanceId,
            'start_time' => '2020-01-02T01:00:00+01:00',
            'user' => 'peter',
            'status' => Status::IN_PROGRESS,
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aProductImportJobInstanceId,
            'start_time' => null,
            'status' => Status::STARTING,
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aProductExportJobInstanceId,
            'start_time' => null,
            'status' => Status::STARTING,
        ]);

        $crashedJobInstance = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_crashed_job',
            'job_name' => 'a_crashed_job',
            'label' => 'A crashed job',
            'type' => 'crashed',
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $crashedJobInstance,
            'start_time' => '2019-01-02T00:00:00+00:00',
            'health_check_time' => '2019-01-02 01:00:00',
            'user' => null,
            'status' => Status::IN_PROGRESS,
        ]);

        $this->stepExecutionIds[] = $this->fixturesJobHelper->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[0],
            'status' => Status::COMPLETED,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'end_time' => '2020-01-01T01:00:05+01:00',
        ]);

        $this->stepExecutionIds[] = $this->fixturesJobHelper->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[0],
            'warning_count' => 2,
            'status' => Status::COMPLETED,
            'start_time' => '2020-01-01T01:00:06+01:00',
            'end_time' => '2020-01-01T01:00:36+01:00',
            'is_trackable' => true,
            'tracking_data' => [
                'totalItems' => 100,
                'processedItems' => 100,
            ],
        ]);

        $this->stepExecutionIds[] = $this->fixturesJobHelper->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[0],
            'warning_count' => 2,
            'status' => Status::COMPLETED,
            'start_time' => '2020-01-01T01:00:37+01:00',
            'end_time' => '2020-01-01T01:00:40+01:00',
            'is_trackable' => true,
            'tracking_data' => [
                'totalItems' => 100,
                'processedItems' => 100,
            ],
        ]);

        $this->stepExecutionIds[] = $this->fixturesJobHelper->createStepExecution([
            'job_execution_id' => $this->jobExecutionIds[1],
            'errors' => [
                'an_error' => 'a backtrace',
                'an_another_error' => 'an another backtrace',
            ],
            'status' => Status::IN_PROGRESS,
            'start_time' => '2020-01-02T01:00:00+01:00',
            'is_trackable' => true,
            'tracking_data' => [
                'totalItems' => 1000,
                'processedItems' => 300,
            ],
        ]);

        $aScheduledJobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_scheduled_import',
            'job_name' => 'a_scheduled_import',
            'label' => 'A scheduled import',
            'type' => 'import',
        ]);

        $this->jobExecutionIds[] = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $aScheduledJobInstanceId,
            'start_time' => '2020-01-01T01:00:00+01:00',
            'user' => 'job_automated_user',
            'status' => Status::COMPLETED,
            'is_stoppable' => false,
        ]);
    }

    private function getExpectedJobExecutionRow(int $jobExecutionId): JobExecutionRow
    {
        if (empty($this->cachedExpectedJobExecutionRows)) {
            $this->cachedExpectedJobExecutionRows = [
                $this->jobExecutionIds[0] => new JobExecutionRow(
                    $this->jobExecutionIds[0],
                    'A product import',
                    'import',
                    new \DateTimeImmutable('2020-01-01T00:00:00+00:00'),
                    'julia',
                    Status::fromLabel('COMPLETED'),
                    false,
                    new JobExecutionTracking(3, 3, [
                        new StepExecutionTracking(
                            $this->stepExecutionIds[0],
                            5,
                            0,
                            false,
                            0,
                            0,
                            false,
                            Status::fromLabel('COMPLETED'),
                        ),
                        new StepExecutionTracking(
                            $this->stepExecutionIds[1],
                            30,
                            2,
                            false,
                            100,
                            100,
                            true,
                            Status::fromLabel('COMPLETED'),
                        ),
                        new StepExecutionTracking(
                            $this->stepExecutionIds[2],
                            3,
                            2,
                            false,
                            100,
                            100,
                            true,
                            Status::fromLabel('COMPLETED'),
                        ),
                    ]),
                ),
                $this->jobExecutionIds[1] => new JobExecutionRow(
                    $this->jobExecutionIds[1],
                    'A product import',
                    'import',
                    new \DateTimeImmutable('2020-01-02T00:00:00+00:00'),
                    'peter',
                    Status::fromLabel('IN_PROGRESS'),
                    true,
                    new JobExecutionTracking(1, 3, [
                        new StepExecutionTracking(
                            $this->stepExecutionIds[3],
                            3600,
                            0,
                            true,
                            1000,
                            300,
                            true,
                            Status::fromLabel('IN_PROGRESS'),
                        ),
                    ]),
                ),
                $this->jobExecutionIds[2] => new JobExecutionRow(
                    $this->jobExecutionIds[2],
                    'A product import',
                    'import',
                    null,
                    null,
                    Status::fromLabel('STARTING'),
                    true,
                    new JobExecutionTracking(0, 3, []),
                ),
                $this->jobExecutionIds[3] => new JobExecutionRow(
                    $this->jobExecutionIds[3],
                    'A product export',
                    'export',
                    null,
                    null,
                    Status::fromLabel('STARTING'),
                    true,
                    new JobExecutionTracking(0, 3, []),
                ),
                $this->jobExecutionIds[4] => new JobExecutionRow(
                    $this->jobExecutionIds[4],
                    'A crashed job',
                    'crashed',
                    new \DateTimeImmutable('2019-01-02T00:00:00+00:00'),
                    null,
                    Status::fromLabel('FAILED'),
                    true,
                    new JobExecutionTracking(0, 3, []),
                ),
                $this->jobExecutionIds[5] => new JobExecutionRow(
                    $this->jobExecutionIds[5],
                    'A scheduled import',
                    'import',
                    new \DateTimeImmutable('2020-01-01T00:00:00+00:00'),
                    'job_automated_user',
                    Status::fromLabel('COMPLETED'),
                    false,
                    new JobExecutionTracking(0, 3, [])
                ),
            ];
        }

        return $this->cachedExpectedJobExecutionRows[$jobExecutionId];
    }
}
