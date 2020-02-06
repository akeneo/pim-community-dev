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
        $query = <<<SQL
SELECT COUNT(*)
FROM akeneo_batch_job_execution_queue AS queue
JOIN akeneo_batch_job_execution AS job_execution ON job_execution.id = queue.job_execution_id
JOIN akeneo_batch_job_instance AS job_instance ON job_instance.id = job_execution.job_instance_id
WHERE job_instance.code = :job_code
SQL;

        $stmt = $this->get('database_connection')->executeQuery(
            $query,
            ['job_code' => 'data_quality_insights_periodic_tasks']
        );

        $count = intval($stmt->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }
}
