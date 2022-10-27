<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Infrastructure\Connector\Tasklet;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueHandlerInterface;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueQuery;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Push scheduled Jobs to queue.
 *
 * @author    Brice LE BOULC'H <brice.leboulch@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PushScheduledJobsToQueueTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'push_scheduled_jobs_to_queue';

    public function __construct(
        private FeatureFlag $jobAutomationFeatureFlag,
        private FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        private PushScheduledJobsToQueueHandlerInterface $pushScheduledJobsToQueueHandler,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        if ($this->jobAutomationFeatureFlag->isEnabled()) {
            $this->pushScheduledJobsToQueueHandler->handle(
                new PushScheduledJobsToQueueQuery($this->findScheduledJobInstancesQuery->all()),
            );
        }
    }
}
