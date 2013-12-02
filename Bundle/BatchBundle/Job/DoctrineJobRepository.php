<?php

namespace Oro\Bundle\BatchBundle\Job;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Class peristing JobExecution and StepExecution states
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.JobRepository
 */
class DoctrineJobRepository implements JobRepositoryInterface
{
    /* @var entityManager */
    protected $entityManager = null;

    /**
     * Provides the doctrine entity manager
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $this->entityManager->persist($jobExecution);
        $this->entityManager->flush($jobExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
        $this->entityManager->persist($stepExecution);
        $this->entityManager->flush($stepExecution);
    }

}
