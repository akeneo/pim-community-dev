<?php

namespace Pim\Bundle\BatchBundle\Job;

/**
 * Mock class implementing job repository interface
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobRepostoryInterface
{
    private $jobExecutions = array();

    /**
     * {@inheritDoc}}
     */
    public function createJobExecution($jobName, JobParameters $jobParameters)
    {
        $ex = new JobExecution();
        $ex->setJobParameters($jobParameters);

        $this->jobExecutions[] = $ex;

        $ex->setId(count($this->jobExecutions) - 1);

        return $ex;
    }

    /**
     * {@inheritDoc}}
     */
    public function updateJobExecution($jobExecution)
    {
    }

    /**
     * {@inheritDoc}}
     */
    public function updateStepExecution($stepExecution)
    {
    }
}
