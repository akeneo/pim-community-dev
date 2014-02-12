<?php

namespace Akeneo\Bundle\BatchBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Event triggered during job execution
 *
 */
class JobExecutionEvent extends Event implements EventInterface
{
    protected $jobExecution;

    public function __construct(JobExecution $jobExecution)
    {
        $this->jobExecution = $jobExecution;
    }

    public function getJobExecution()
    {
        return $this->jobExecution;
    }
}
