<?php
namespace Pim\Bundle\BatchBundle\Job;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A job instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobFactory
{
    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @param JobRepositoryInterface $jobRepository Object responsible
     *     for persisting jobExecution and stepExection states
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
