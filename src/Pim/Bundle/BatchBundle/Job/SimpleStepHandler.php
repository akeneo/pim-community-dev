<?php

namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Step\StepInterface;
use Pim\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Implementation of {@link StepHandler} that manages repository and restart
 * concerns.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.SimpleStepHandler
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleStepHandler implements StepHandlerInterface
{
    /* @var JobRepositoryInterface */
    private $jobRepository = null;

    /* @var ExecutionContext $executionContext */
    private $executionContext = null;

    private $logger = null;

    /**
     * @param object                 $logger
     * @param JobRepositoryInterface $jobRepository    Job repository
     * @param ExecutionContext       $executionContext Execution context
     */
    public function __construct(
        $logger,
        JobRepositoryInterface $jobRepository,
        ExecutionContext $executionContext = null
    ) {
        $this->jobRepository = $jobRepository;
        if ($executionContext = null) {
            $executionContext = new ExecutionContext();
        }
        $this->executionContext = $executionContext;
        $this->logger = $logger;
    }

    /**
     * Set the logger
     *
     * @param object $logger The logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the logger for internal use
     * @return object
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param JobRepositoryInterface $jobRepository the jobRepository to set
     */
    public function setJobRepository(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * A context containing values to be added to the step execution before it
     * is handled.
     *
     * @param ExecutionContext $executionContext the execution context to set
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;
    }

    /**
     * @param StepInterface $step      Step
     * @param JobExecution  $execution Job execution
     *
     * @throws JobInterruptedException
     * @throws JobRestartException
     * @throws StartLimitExceededException
     *
     * @return mixed
     */
    public function handleStep(StepInterface $step, JobExecution $execution)
    {
        if ($execution->isStopping()) {
            throw new JobInterruptedException("JobExecution interrupted.");
        }

        $currentStepExecution = $execution->createStepExecution($step->getName());

        $this->getLogger()->info("Executing step: [" . $step->getName() . "]");
        try {
            $step->execute($currentStepExecution);
        } catch (JobInterruptedException $e) {
            // Ensure that the job gets the message that it is stopping
            // and can pass it on to other steps that are executing
            // concurrently.
            $execution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw $e;
        }

        if ($currentStepExecution->getStatus()->getValue() == BatchStatus::STOPPING
                || $currentStepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
            // Ensure that the job gets the message that it is stopping
            $execution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw new JobInterruptedException("Job interrupted by step execution");
        }

        return $currentStepExecution;
    }
}
