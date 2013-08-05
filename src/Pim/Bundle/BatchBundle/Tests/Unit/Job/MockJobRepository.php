<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Pim\Bundle\BatchBundle\Entity\Job as JobEntity;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

use Pim\Bundle\BatchBundle\Job\JobRepositoryInterface;

/**
 * Mock class implementing job repository interface
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MockJobRepository implements JobRepositoryInterface
{
    private $jobExecutions = array();

    /**
     * {@inheritDoc}}
     */
    public function createJobExecution(JobEntity $job)
    {
        $jobExecution = new JobExecution();

        $this->jobExecutions[] = $jobExecutions;

        $jobExecution->setId(count($this->jobExecutions) - 1);

        return $ex;
    }

    /**
     * {@inheritDoc}}
     */
    public function updateJobExecution(JobExecution $jobExecution)
    {
    }

    /**
     * {@inheritDoc}}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
    }

    /**
     * {@inheritDoc}}
     */
    public function flush()
    {
    }
}
