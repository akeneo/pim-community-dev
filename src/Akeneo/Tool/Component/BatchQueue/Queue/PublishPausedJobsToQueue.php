<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\SqlGetPausedJobExecutionIds;

/**
 * Push a paused job instance to resume into the job execution queue.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class PublishPausedJobsToQueue
{

    public function __construct(
        private readonly JobExecutionQueueInterface $jobExecutionQueue,
        private readonly SqlGetPausedJobExecutionIds $getPausedJobExecutionIds
    )
    {}

    public function publishPausedJobs(): void
    {
        $jobExecutionIds = $this->getPausedJobExecutionIds->all();

        foreach ($jobExecutionIds as $jobExecutionId) {
            $jobExecutionMessage = PausedJobExecutionMessage::createJobExecutionMessage($jobExecutionId, []);
            $this->jobExecutionQueue->publish($jobExecutionMessage);
        }
    }

}