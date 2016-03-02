<?php

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A job instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobFactory
{
    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher
     * @param JobRepositoryInterface   $jobRepository   Object responsible
     *                                                  for persisting jobExecution and stepExection states
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, JobRepositoryInterface $jobRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository   = $jobRepository;
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
        $job->setJobRepository($this->jobRepository);
        $job->setEventDispatcher($this->eventDispatcher);

        return $job;
    }
}
