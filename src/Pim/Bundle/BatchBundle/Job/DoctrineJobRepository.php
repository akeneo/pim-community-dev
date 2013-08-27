<?php

namespace Pim\Bundle\BatchBundle\Job;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Class peristing JobExecution and StepExecution states
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.JobRepository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    }

    /**
     * {@inheritdoc}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
        $this->entityManager->persist($stepExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->entityManager->flush();
    }
}
