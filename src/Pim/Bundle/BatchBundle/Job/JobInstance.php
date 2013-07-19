<?php

namespace Pim\Bundle\BatchBundle\Job;

/**
 * Batch domain object representing a uniquely identifiable job run.
 * JobInstance can be restarted multiple times in case of execution failure and
 * it's lifecycle ends with first successful execution.
 *
 * Trying to execute an existing JobIntance that has already completed
 * successfully will result in error. Error will be raised also for an attempt
 * to restart a failed JobInstance if the Job is not restartable.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.JobInstance;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobInstance
{
    private $id;

    private $jobName;

    /**
     * Constructor
     * @param integer $id      Id of the job instance
     * @param string  $jobName Name of the job
     */
    public function __construct($id, $jobName)
    {
        $this->id = $id;
        $this->jobName = $jobName;
    }

    /**
     * @return the job name.
     */
    public function getJobName()
    {
        return $this->jobName;
    }

    /**
     * To string
     * @return string
     */
    public function __toString()
    {
        return sprintf("Id=[%s], Job=[%s]", $this->id, $this->jobName);
    }
}
