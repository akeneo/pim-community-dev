<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Connector\JobLauncher;

use Akeneo\Test\Integration\TestCase;

final class SchedulePeriodicTasksIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeJobs')->initialize();
    }

    public function test_it_schedules_periodic_tasks(): void
    {
        $this->assertCountScheduledPeriodicTasks(0);

        $this->get('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\SchedulePeriodicTasks')
            ->schedule(new \DateTimeImmutable());

        $this->assertCountScheduledPeriodicTasks(1);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertCountScheduledPeriodicTasks(int $expectedCount): void
    {
        $jobTransport = $this->get('messenger.transport.data_maintenance_job');

        $count = 0;
        $stop = false;
        while (!$stop) {
            $stop = true;

            foreach ($jobTransport->get() as $envelope) {
                $stop = false;
                $jobExecutionId = $envelope->getMessage()->getJobExecutionId();
                if ('data_quality_insights_periodic_tasks' === $this->getJobCodeFromJobExecutionId($jobExecutionId)) {
                    $count++;
                }
            }
        }

        $this->assertSame($expectedCount, $count);
    }

    private function getJobCodeFromJobExecutionId(int $jobExecutionId): string
    {
        $query = <<<SQL
SELECT job_instance.code
FROM akeneo_batch_job_execution job_execution
    JOIN akeneo_batch_job_instance AS job_instance ON job_instance.id = job_execution.job_instance_id
WHERE job_execution.id = :job_execution_id
SQL;

        $stmt = $this->get('database_connection')->executeQuery(
            $query,
            ['job_execution_id' => $jobExecutionId]
        );
        $jobCode = $stmt->fetchColumn();
        self::assertIsString($jobCode, 'Job code cannot be found.');

        return $jobCode;
    }
}
