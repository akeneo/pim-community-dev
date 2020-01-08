<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

/**
 * This class aims to publish and consume job execution messages into a queue.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobExecutionQueueInterface
{
    /**
     * Publishes a message into the queue.
     *
     * @param JobExecutionMessage $jobExecutionMessage
     */
    public function publish(JobExecutionMessage $jobExecutionMessage): void;

    /**
     * Gets the last job execution message from the queue, that is not consumed yet.
     * This method loops until there is a message to consume into the queue.
     *
     * @param string $consumer name of the consumer
     * @param string[] $whitelistedJobInstanceCodes codes of the whitelisted job instances
     * @param string[] $blacklistedJobInstanceCodes codes of the blacklisted job instances
     *
     * @return JobExecutionMessage
     */
    public function consume(string $consumer, array $whitelistedJobInstanceCodes = [], array $blacklistedJobInstanceCodes = []): JobExecutionMessage;
}
