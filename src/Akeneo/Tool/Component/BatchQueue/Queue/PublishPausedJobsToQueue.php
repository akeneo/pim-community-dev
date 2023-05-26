<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\GetPausedJobExecutionIdsInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PublishPausedJobsToQueue
{
    public function __construct(
        private readonly JobExecutionQueueInterface $jobExecutionQueue,
        private readonly GetPausedJobExecutionIdsInterface $getPausedJobExecutionIds,
    ) {
    }

    public function publish(): void
    {
        $jobExecutionIds = $this->getPausedJobExecutionIds->all();

        foreach ($jobExecutionIds as $jobExecutionId) {
            // Should we take care of the Tenant ID here ?
            $jobExecutionMessage = PausedJobExecutionMessage::createJobExecutionMessage($jobExecutionId, []);
            // Try catch => Log => Alert Datadog
            $this->jobExecutionQueue->publish($jobExecutionMessage);
        }
    }
}
