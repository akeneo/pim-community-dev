<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Hydrator;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobExecutionRowHydrator
{
    public function __construct(
        private JobExecutionTrackingHydrator $jobExecutionTrackingHydrator,
        private JobExecutionHealthCheckHydrator $jobExecutionRowHydrator,
    ) {
    }

    public function hydrate(array $jobExecution): JobExecutionRow
    {
        $startTime = null !== $jobExecution['start_time'] ?
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $jobExecution['start_time'], new \DateTimeZone('UTC'))
            : null;

        $tracking = $this->jobExecutionTrackingHydrator->hydrate(
            (int) ($jobExecution['current_step_number'] ?? 0),
            (int) $jobExecution['step_count'],
            json_decode($jobExecution['steps'], true),
        );

        $healthCheck = $this->jobExecutionRowHydrator->hydrate(
            (int) $jobExecution['status'],
            $jobExecution['health_check_time'],
        );

        return new JobExecutionRow(
            (int) $jobExecution['id'],
            $jobExecution['label'],
            $jobExecution['type'],
            $startTime,
            $jobExecution['user'],
            $healthCheck,
            (bool) $jobExecution['is_stoppable'],
            $tracking,
        );
    }
}
