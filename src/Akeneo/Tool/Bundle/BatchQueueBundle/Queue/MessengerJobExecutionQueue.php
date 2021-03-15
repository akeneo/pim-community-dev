<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobQueueConsumerConfiguration;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class MessengerJobExecutionQueue implements JobExecutionQueueInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function publish(JobExecutionMessageInterface $jobExecutionMessage): void
    {
        $this->bus->dispatch($jobExecutionMessage);
    }

    public function consume(string $consumer, JobQueueConsumerConfiguration $configuration): ?JobExecutionMessageInterface
    {
        throw new \LogicException('You cannot consume messages here. Use symfony messenger command to consume.');
    }
}
