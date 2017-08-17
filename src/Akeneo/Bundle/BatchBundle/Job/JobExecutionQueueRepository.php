<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Component\Batch\Job\JobExecutionQueueRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecutionMessage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionQueueRepository implements JobExecutionQueueRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(JobExecutionMessage $jobExecutionMessage) : void
    {
        $jobExecution = $this->entityManager->merge($jobExecutionMessage->getJobExecution());

        $jobExecutionMessage = new JobExecutionMessage(
            $jobExecution,
            $jobExecutionMessage->getCommandName(),
            $jobExecutionMessage->getOptions()
        );

        $this->entityManager->persist($jobExecutionMessage);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastJobExecutionMessage() : ?array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('q.id')
            ->addSelect('q.job_execution_id')
            ->addSelect('q.command_name')
            ->addSelect('q.options')
            ->addSelect('q.consumer')
            ->addSelect('ji.code as job_instance_code')

            ->from('akeneo_batch_job_execution_queue', 'q')
            ->join('q', 'akeneo_batch_job_execution', 'je', 'q.job_execution_id=je.id')
            ->join('je', 'akeneo_batch_job_instance', 'ji', 'je.job_instance_id=ji.id')

            ->where($queryBuilder->expr()->isNull('q.consumer'))

            ->orderBy('q.create_time')
            ->addOrderBy('q.id')

            ->setMaxResults(1);

        $stmt = $queryBuilder->execute();
        $stmt->execute();
        $data = $stmt->fetch();

        return false !== $data ? $data : null;
    }


    /**
     * {@inheritdoc}
     */
    public function updateConsumerName(string $jobExecutionMessageId, string $consumer) : void
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->update('akeneo_batch_job_execution_queue', 'q')
            ->set('q.consumer', ':consumer')
            ->set('q.updated_time', ':updated_date')
            ->where($queryBuilder->expr()->eq('q.id', ':id'))
            ->setParameter('id', $jobExecutionMessageId)
            ->setParameter('updated_date', new \DateTime('now', new \DateTimeZone('UTC')), 'datetime')
            ->setParameter('consumer', $consumer);

        $queryBuilder->execute();
    }
}
