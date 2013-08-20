<?php

use Monolog\Logger;

namespace Pim\Bundle\BatchBundle\Job;

/**
 * A job instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobFactory
{
    protected $logger;

    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /* @var StepHandlerInterface */
    protected $stepHandler;

    /**
     * @param Logger $logger Logger where to log output of the job
     * @param JobRepositoryInterface $jobRepository Object responsible for persisting jobExecution and stepExection states
     * @param StepHandlerInterface $stepHandler Object to which the step management is delegated
     */
    public function __construct(Logger $logger, JobRepositoryInterface $jobRepository, StepHandlerInterface $stepHandler)
    {
        $this->logger        = $logger;
        $this->jobRepository = $jobRepository;
        $this->stepHandler   = $stepHandler;
    }

    /**
     * Create a job object
     *
     * @param string $title Title of the Job Object
     *
     * @return Job $job The created job
     */
    public function createJob($title)
    {
        $job = new Job($title);
        $job->setLogger($this->logger);
        $job->setJobRepository($this->jobRepository);
        $job->setStepHandler($this->stepHandler);

        return $job;
    }
}
