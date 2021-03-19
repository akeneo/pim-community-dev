<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobQueueConsumerConfiguration;

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
     */
    public function publish(JobExecutionMessageInterface $jobExecutionMessage): void;

    /**
     * @TODO CPM-152: to remove
     * Gets the last job execution message from the queue, that is not consumed yet.
     */
    public function consume(string $consumer, JobQueueConsumerConfiguration $configuration): ?JobExecutionMessageInterface;
}
