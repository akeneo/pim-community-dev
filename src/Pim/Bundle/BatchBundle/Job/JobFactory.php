<?php

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
    protected $jobRepository;
    protected $stepHandler;

    public function __construct($logger, $jobRepository, $stepHandler)
    {
        $this->logger        = $logger;
        $this->jobRepository = $jobRepository;
        $this->stepHandler   = $stepHandler;
    }

    public function createJob($title)
    {
        $job = new Job($title);
        $job->setLogger($this->logger);
        $job->setJobRepository($this->jobRepository);
        $job->setStepHandler($this->stepHandler);

        return $job;
    }
}

