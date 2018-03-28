<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\Queue;

use Akeneo\Bundle\BatchQueueBundle\Hydrator\JobExecutionMessageHydrator;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Provider\NodeProviderInterface;

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
    /** Interval in seconds before checking if a new message is in the queue. */
    const QUEUE_CHECK_INTERVAL = 5;

    /** Prefix to add when locking a job execution message in database. */
    const LOCK_PREFIX = 'akeneo_job_execution_';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var JobExecutionMessageRepository */
    private $jobExecutionMessageRepository;

    /**
     * @param EntityManagerInterface        $entityManager
     * @param JobExecutionMessageRepository $jobExecutionMessageRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        JobExecutionMessageRepository $jobExecutionMessageRepository
    ) {
        $this->entityManager = $entityManager;
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
    public function consume(string $consumer): JobExecutionMessage
    {
        $hasLock = false;

        do {
            $jobExecutionMessage = $this->jobExecutionMessageRepository->getAvailableJobExecutionMessage();
            if (null !== $jobExecutionMessage) {
                $lock = self::LOCK_PREFIX . $jobExecutionMessage->getJobExecutionId();
                $hasLock = $this->lock($lock);
            }
        } while (!$hasLock && 0 === sleep(self::QUEUE_CHECK_INTERVAL));

        $jobExecutionMessage->consumedBy($consumer);

        $this->jobExecutionMessageRepository->updateJobExecutionMessage($jobExecutionMessage);
        $this->unlock($lock);

        return $jobExecutionMessage;
    }

    /**
     * Try to get an application-level lock.
     *
     * @param string $lock
     *
     * @return bool return true if lock has been acquired, false otherwise
     */
    private function lock(string $lock): bool
    {
        $stmt = $this->entityManager->getConnection()->prepare('SELECT GET_LOCK(:lock, 0)');
        $stmt->bindValue('lock', $lock);

        $stmt->execute();
        $result = $stmt->fetch();

        return '1' === current($result);
    }

    /**
     * Release an application-level lock.
     *
     * @param string $lock
     */
    private function unlock(string $lock): void
    {
        $stmt = $this->entityManager->getConnection()->prepare('SELECT RELEASE_LOCK(:lock)');
        $stmt->bindValue('lock', $lock);

        $stmt->execute();
    }
}
