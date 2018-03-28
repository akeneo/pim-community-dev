<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\Queue;

use Akeneo\Bundle\BatchQueueBundle\Hydrator\JobExecutionMessageHydrator;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository to persist and get the state of the job execution messages in the queue.
 *
 * As it used by a daemon, it uses directly the DBAL to avoid any memory leak or connection problem due to the UOW.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionMessageRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var JobExecutionMessageHydrator */
    private $jobExecutionHydrator;

    /**
     * @param EntityManagerInterface      $entityManager
     * @param JobExecutionMessageHydrator $jobExecutionHydrator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        JobExecutionMessageHydrator $jobExecutionHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->jobExecutionHydrator = $jobExecutionHydrator;
    }

    /**
     * @param JobExecutionMessage $jobExecutionMessage
     */
    public function createJobExecutionMessage(JobExecutionMessage $jobExecutionMessage)
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_execution_queue (job_execution_id, options, consumer, create_time, updated_time)
VALUES (:job_execution_id, :options, :consumer, :create_time, :updated_time)
SQL;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('job_execution_id', $jobExecutionMessage->getJobExecutionId());
        $stmt->bindValue('options', $jobExecutionMessage->getOptions(), Type::JSON_ARRAY);
        $stmt->bindValue('consumer', null);
        $stmt->bindValue('create_time', new \DateTime('now', new \DateTimeZone('UTC')), Type::DATETIME);
        $stmt->bindValue('updated_time', null);

        $stmt->execute();
    }

    /**
     * @param JobExecutionMessage $jobExecutionMessage
     */
    public function updateJobExecutionMessage(JobExecutionMessage $jobExecutionMessage)
    {
        $sql = <<<SQL
UPDATE 
    akeneo_batch_job_execution_queue q
SET 
    q.job_execution_id = :job_execution_id,
    q.options = :options,
    q.consumer = :consumer,
    q.create_time = :create_time,
    q.updated_time = :updated_time
WHERE
    q.id = :id;
SQL;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('job_execution_id', $jobExecutionMessage->getJobExecutionId());
        $stmt->bindValue('options', $jobExecutionMessage->getOptions(), Type::JSON_ARRAY);
        $stmt->bindValue('consumer', $jobExecutionMessage->getConsumer());
        $stmt->bindValue('create_time', $jobExecutionMessage->getCreateTime(), Type::DATETIME);
        $stmt->bindValue('updated_time', new \DateTime('now', new \DateTimeZone('UTC')), Type::DATETIME);
        $stmt->bindValue('id', $jobExecutionMessage->getId());
        $stmt->execute();
    }

    /**
     * Gets a job execution message that has not been consumed yet.
     * If there is no job execution available, it returns null.
     *
     * @return JobExecutionMessage|null
     */
    public function getAvailableJobExecutionMessage(): ?JobExecutionMessage
    {
        $sql = <<<SQL
SELECT 
    q.id, q.job_execution_id, q.create_time, q.updated_time, q.options, q.consumer
FROM
    akeneo_batch_job_execution_queue q
WHERE
    q.consumer IS NULL
ORDER BY
    q.create_time, id
LIMIT 1;
SQL;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();

        return false !== $row ? $this->jobExecutionHydrator->hydrate($row) : null;
    }

    /**
     * Gets the job instance code associated to a job execution message.
     *
     * @param JobExecutionMessage $jobExecutionMessage
     *
     * @return string|null
     */
    public function getJobInstanceCode(JobExecutionMessage $jobExecutionMessage): ?string
    {
        $sql = <<<SQL
SELECT 
    code
FROM
    akeneo_batch_job_execution je 
JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
WHERE 
    je.id = :id
SQL;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('id', $jobExecutionMessage->getJobExecutionId());
        $stmt->execute();
        $data = $stmt->fetch();

        $code = $data['code'] ?? null;

        return $code;
    }
}
