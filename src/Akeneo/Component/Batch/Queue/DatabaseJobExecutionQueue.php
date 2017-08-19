<?php

declare(strict_types=1);

namespace Akeneo\Component\Batch\Queue;

use Akeneo\Component\Batch\Job\JobExecutionQueueRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecutionMessage;

/**
 * Push job execution messages in a queue stored in database.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabaseJobExecutionQueue implements JobExecutionQueueInterface
{
    /** @var JobExecutionQueueRepositoryInterface */
    protected $jobExecutionQueueRepository;

    /**
     * @param JobExecutionQueueRepositoryInterface $jobExecutionQueueRepository
     */
    public function __construct(JobExecutionQueueRepositoryInterface $jobExecutionQueueRepository)
    {
        $this->jobExecutionQueueRepository = $jobExecutionQueueRepository;
    }

    /**
     * @inheritDoc
     */
    public function publish(JobExecutionMessage $jobExecutionMessage) : void
    {
        $this->jobExecutionQueueRepository->publish($jobExecutionMessage);
    }
}
