<?php

declare(strict_types=1);

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobExecutionMessage;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;

/**
 * Common interface to manage how Job Execution Messages are saved and found into the Job Execution Queue.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobExecutionQueueRepositoryInterface
{
    /**
     * Publish a job execution message into the queue.
     *
     * @param JobExecutionMessage $jobExecutionMessage
     */
    public function publish(JobExecutionMessage $jobExecutionMessage) : void;

    /**
     * Get the last job execution message from the queue, that is not consumed yet.
     *
     * As this function is called by the consumers of the queue, and that these consumers are daemon processes,
     * it uses directly the DBAL in order to avoid potential memory leak due to Doctrine hydratation.
     *
     * @return array|null returns an array with the job execution message data or null if no available job message is found
     */
    public function getLastJobExecutionMessage() : ?array;

    /**
     * Update the job execution message, for a given id, with the consumer's name of the message.
     *
     * @param string $jobExecutionMessageJobExecutionMessageId
     * @param string $consumer
     */
    public function updateConsumerName(string $jobExecutionMessageJobExecutionMessageId, string $consumer) : void;
}
