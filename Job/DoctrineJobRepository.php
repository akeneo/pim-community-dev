<?php

namespace Oro\Bundle\BatchBundle\Job;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Class peristing JobExecution and StepExecution states.
 * This class instantiates a specific EntityManager to avoid
 * polluting the transactional state of data coming through the
 * batch.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.JobRepository
 */
class DoctrineJobRepository implements JobRepositoryInterface
{
    /* @var EntityManager */
    protected $jobManager = null;

    /**
     * Provides the doctrine entity manager
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $currentConn = $entityManager->getConnection();

        $jobConn = new Connection(
            $currentConn->getParams(),
            $currentConn->getDriver(),
            $currentConn->getConfiguration()
        );

        $jobManager = EntityManager::create(
            $jobConn,
            $entityManager->getConfiguration()
        );

        $this->jobManager = $jobManager;
    }

    /**
     * Get the specific Job entityManager
     *
     * @return EntityManager
     */
    public function getJobManager()
    {
        return $this->jobManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createJobExecution(JobInstance $jobInstance)
    {
        $jobExecution = new JobExecution();
        $jobInstance->addJobExecution($jobExecution);

        return $jobExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function updateJobExecution(JobExecution $jobExecution)
    {
        $this->jobManager->persist($jobExecution);
        $this->jobManager->flush($jobExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
        $this->jobManager->persist($stepExecution);
        $this->jobManager->flush($stepExecution);
    }
}
