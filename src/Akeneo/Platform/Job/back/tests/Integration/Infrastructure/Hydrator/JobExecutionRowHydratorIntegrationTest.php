<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Hydrator;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Infrastructure\Hydrator\JobExecutionRowHydrator;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

/**
 * MySQL 8.4 changed GREATEST(DATETIME, INT) to return DATETIME(6) instead of DOUBLE.
 * JobExecutionRowHydrator uses createFromFormat('Y-m-d H:i:s', ...) which cannot parse
 * a value like '2020-01-01 12:00:00.000000' — it returns false, causing a TypeError
 * when the result is passed to the JobExecutionRow constructor.
 */
class JobExecutionRowHydratorIntegrationTest extends IntegrationTestCase
{
    private JobExecutionRowHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydrator = $this->get(JobExecutionRowHydrator::class);
    }

    /**
     * This test skips on MySQL 8.0 (GREATEST returns a DOUBLE numeric string) and fails on
     * MySQL 8.4 before JobExecutionRowHydrator is fixed to handle the DATETIME(6) format.
     */
    public function testHydrateCanHandleStartTimeDatetime6FormatFromMysql84(): void
    {
        $jobInstanceId = $this->fixturesJobHelper->createJobInstance([
            'code' => 'mysql84_hydrator_test',
            'job_name' => 'mysql84_hydrator_test',
            'label' => 'MySQL 8.4 Hydrator Test',
            'type' => 'import',
        ]);

        // health_check_time is NULL so GREATEST(start_time, COALESCE(health_check_time, 0))
        // becomes GREATEST(start_time, 0) — on MySQL 8.4 this returns DATETIME(6) format.
        $jobExecutionId = $this->fixturesJobHelper->createJobExecution([
            'job_instance_id' => $jobInstanceId,
            'start_time' => '2020-01-01 12:00:00',
            'status' => Status::COMPLETED,
            'is_stoppable' => false,
        ]);

        // GREATEST(DATETIME_col, COALESCE(nullable_DATETIME_col, 0)) with the second column NULL:
        // - MySQL 8.0: promotes to DOUBLE (returns a large numeric string)
        // - MySQL 8.4: promotes to DATETIME(6) (returns e.g. '2020-01-01 12:00:00.000000')
        // A literal COALESCE(NULL, 0) does NOT trigger this — it must be a typed DATETIME column.
        // health_check_time is a nullable DATETIME column; the job execution we created has no
        // health_check_time, so COALESCE(je.health_check_time, 0) returns 0 with DATETIME typing.
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

        // On MySQL 8.0 $startTimeValue is e.g. '2020-01-01 12:00:00' (no microseconds) — passes.
        // On MySQL 8.4 $startTimeValue is e.g. '2020-01-01 12:00:00.000000' — fails.
        // JobExecutionRowHydrator::hydrate() calls createFromFormat('Y-m-d H:i:s', ...) which
        // returns false for strings with trailing microseconds, causing a TypeError.
        $result = $this->hydrator->hydrate($row);

        $this->assertInstanceOf(JobExecutionRow::class, $result);
        $normalized = $result->normalize();
        $this->assertNotNull($normalized['started_at'], 'started_at must not be null after hydrating a DATETIME(6) start_time');
    }
}
