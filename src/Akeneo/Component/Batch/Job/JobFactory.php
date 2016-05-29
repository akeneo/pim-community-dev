<?php

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A job factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * TODO: to be dropped
 */
class JobFactory
{
    /* @var EventDispatcherInterface */
    protected $eventDispatcher;

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
     * @param string $name Name of the Job Object
     *
     * @return Job $job The created job
     */
    public function createJob($name)
    {
        $job = new Job($name, $this->eventDispatcher, $this->jobRepository);

        return $job;
    }
}
