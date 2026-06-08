<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Hydrator;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Infrastructure\Hydrator\JobExecutionRowHydrator;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class JobExecutionRowHydratorIntegrationTest extends IntegrationTestCase
{
    private JobExecutionRowHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydrator = $this->get(JobExecutionRowHydrator::class);
    }

    /**
     * MySQL 8.4 changed GREATEST(DATETIME, COALESCE(nullable_DATETIME_col, 0)) to return
     * DATETIME(6) (e.g. '2020-01-01 12:00:00.000000') instead of a plain DATETIME string.
     * A literal COALESCE(NULL, 0) does NOT trigger this — the fallback must be a typed DATETIME
     * column; here health_check_time is that nullable DATETIME column and is NULL on the created
     * execution, so COALESCE(je.health_check_time, 0) provides the typed-NULL side.
     * JobExecutionRowHydrator::hydrate() calls createFromFormat('Y-m-d H:i:s', ...) which returns
     * false for strings with trailing microseconds, causing a TypeError in the JobExecutionRow
     * constructor.
     * Passes on MySQL 8.0 (no microseconds), fails on MySQL 8.4 before the fix.
     */
    public function testHydrateCanHandleStartTimeDatetime6FormatFromMysql84(): void
    {
        $jobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'mysql84_hydrator_test',
            'job_name' => 'mysql84_hydrator_test',
            'label' => 'MySQL 8.4 Hydrator Test',
            'type' => 'import',
        ]);

        $jobExecutionId = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $jobInstanceId,
            'start_time' => '2020-01-01 12:00:00',
            'status' => Status::COMPLETED,
            'is_stoppable' => false,
        ]);

        $row = $this->get('database_connection')->executeQuery(
            <<<SQL
            SELECT
                je.id,
                ji.label,
                ji.type,
                GREATEST(je.start_time, COALESCE(je.health_check_time, 0)) AS start_time,
                je.user,
                je.status AS calculated_status,
                je.is_stoppable,
                je.step_count,
                '[]' AS steps,
                0 AS current_step_number
            FROM akeneo_batch_job_execution je
            JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id
            WHERE je.id = :id
            SQL,
            ['id' => $jobExecutionId]
        )->fetchAssociative();

        $this->assertIsArray($row);

        $result = $this->hydrator->hydrate($row);

        $this->assertInstanceOf(JobExecutionRow::class, $result);
        $normalized = $result->normalize();
        $this->assertNotNull($normalized['started_at']);
    }
}
