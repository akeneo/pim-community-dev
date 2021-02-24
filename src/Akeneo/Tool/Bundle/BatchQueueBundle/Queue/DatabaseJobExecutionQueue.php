<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobQueueConsumerConfiguration;

/**
 * Aims to publish and consume job execution messages in a queue stored in database.
 *
 * It uses directly the DBAL to avoid any memory leak or connection problem due to the Unit of Work.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabaseJobExecutionQueue implements JobExecutionQueueInterface
{
    /** @var JobExecutionMessageRepository */
    private $jobExecutionMessageRepository;

    public function __construct(JobExecutionMessageRepository $jobExecutionMessageRepository)
    {
        $this->jobExecutionMessageRepository = $jobExecutionMessageRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(JobExecutionMessage $jobExecutionMessage): void
    {
        $this->jobExecutionMessageRepository->createJobExecutionMessage($jobExecutionMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function consume(string $consumer, JobQueueConsumerConfiguration $configuration): ?JobExecutionMessage
    {
        $hasBeenUpdated = false;
        $jobExecutionMessage = null;
        $ttl = $configuration['timeToLive'];

        do {
            if (empty($configuraion['whitelistedJobInstanceCodes']) && empty($configuration['blacklistedJobInstanceCodes'])) {
                $jobExecutionMessage = $this->jobExecutionMessageRepository->getAvailableJobExecutionMessage();
            } elseif ($configuration['whitelistedJobInstanceCodes']) {
                $jobExecutionMessage = $this->jobExecutionMessageRepository->getAvailableJobExecutionMessageFilteredByCodes($configuration['whitelistedJobInstanceCodes']);
            } elseif ($configuration['blacklistedJobInstanceCodes']) {
                $jobExecutionMessage = $this->jobExecutionMessageRepository->getAvailableNotBlacklistedJobExecutionMessageFilteredByCodes($configuration['blacklistedJobInstanceCodes']);
            }

            if (null !== $jobExecutionMessage) {
                $jobExecutionMessage->consumedBy($consumer);
                $hasBeenUpdated = $this->jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage);
            }
        } while (!$hasBeenUpdated && 0 !== --$ttl && 0 === sleep($configuration['queueCheckInterval']));

        return $jobExecutionMessage;
    }
}
