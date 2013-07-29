<?php

namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Step\StepInterface;

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
    /* @var JobRepository $jobRepository */
    private $jobRepository = null;

    /* @var ExecutionContext $executionContext */
    private $executionContext = null;

    private $logger = null;

    /**
     * @param JobRepository    $jobRepository    Job repository
     * @param ExecutionContext $executionContext Execution context
     */
    public function __construct(JobRepository $jobRepository, ExecutionContext $executionContext = null)
    {
        $this->jobRepository = $jobRepository;
        if ($executionContext = null) {
            $executionContext = new ExecutionContext();
        }
        $this->executionContext = $executionContext;
    }

    /**
     * Set the logger
     *
     * @param $logger The logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the logger for internal use
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param jobRepository the jobRepository to set
     */
    public function setJobRepository(JobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * A context containing values to be added to the step execution before it
     * is handled.
     *
     * @param executionContext the execution context to set
     */
    /*
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;
    }
    */

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

        /*
        JobInstance jobInstance = execution.getJobInstance();

        StepExecution lastStepExecution = jobRepository.getLastStepExecution(jobInstance, step.getName());
        if (stepExecutionPartOfExistingJobExecution(execution, lastStepExecution)) {
            // If the last execution of this step was in the same job, it's
            // probably intentional so we want to run it again...
            logger.info(String.format("Duplicate step [%s] detected in execution of job=[%s]. "
                    + "If either step fails, both will be executed again on restart.", step.getName(), jobInstance
                    .getJobName()));
            lastStepExecution = null;
        }
        StepExecution currentStepExecution = lastStepExecution;
        if ($this->shouldStart(lastStepExecution, jobInstance, step)) {

            currentStepExecution = execution.createStepExecution(step.getName());

            boolean isRestart = (lastStepExecution != null && !lastStepExecution.getStatus().equals(
                    BatchStatus.COMPLETED));

            if (isRestart) {
                currentStepExecution.setExecutionContext(lastStepExecution.getExecutionContext());
            } else {
                currentStepExecution.setExecutionContext(new ExecutionContext(executionContext));
            }

            jobRepository.add(currentStepExecution);
            */
        $this->getLogger()->info("Executing step: [" . $step->getName() . "]");
        try {
            $step->execute($currentStepExecution);
        } catch (JobInterruptedException $e) {
            // Ensure that the job gets the message that it is stopping
            // and can pass it on to other steps that are executing
            // concurrently.
            $this->execution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw $e;
        }

        //jobRepository.updateExecutionContext(execution);

        if ($currentStepExecution->getStatus()->getValue() == BatchStatus::STOPPING
                || $currentStepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
            // Ensure that the job gets the message that it is stopping
            $this->execution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw new JobInterruptedException("Job interrupted by step execution");
        }
            /*

        } else {
            // currentStepExecution.setExitStatus(ExitStatus.NOOP);
        }
        */

        return $currentStepExecution;
    }

    /**
     * Detect whether a step execution belongs to this job execution.
     * @param jobExecution the current job execution
     * @param stepExecution an existing step execution
     * @return
     */
    /*
    private boolean stepExecutionPartOfExistingJobExecution(JobExecution jobExecution, StepExecution stepExecution) {
        return stepExecution != null && stepExecution.getJobExecutionId() != null
                && stepExecution.getJobExecutionId().equals(jobExecution.getId());
    }
    */

    /**
     * Given a step and configuration, return true if the step should start,
     * false if it should not, and throw an exception if the job should finish.
     * @param lastStepExecution the last step execution
     * @param jobInstance
     * @param step
     *
     * @throws StartLimitExceededException if the start limit has been exceeded
     * for this step
     * @throws JobRestartException if the job is in an inconsistent state from
     * an earlier failure
     */
    /*
    private boolean shouldStart(StepExecution lastStepExecution, JobInstance jobInstance, Step step)
            throws JobRestartException, StartLimitExceededException {

        BatchStatus stepStatus;
        if (lastStepExecution == null) {
            stepStatus = BatchStatus.STARTING;
        } else {
            stepStatus = lastStepExecution.getStatus();
        }

        if (stepStatus == BatchStatus.UNKNOWN) {
            throw new JobRestartException("Cannot restart step from UNKNOWN status. "
                    + "The last execution ended with a failure that could not be rolled back, "
                    + "so it may be dangerous to proceed. Manual intervention is probably necessary.");
        }

        if ((stepStatus == BatchStatus.COMPLETED && step.isAllowStartIfComplete() == false)
                || stepStatus == BatchStatus.ABANDONED) {
            // step is complete, false should be returned, indicating that the
            // step should not be started
            logger.info("Step already complete or not restartable, so no action to execute: " + lastStepExecution);

            return false;
        }

        if (jobRepository.getStepExecutionCount(jobInstance, step.getName()) < step.getStartLimit()) {
            // step start count is less than start max, return true
            return true;
        } else {
            // start max has been exceeded, throw an exception.
            throw new StartLimitExceededException("Maximum start limit exceeded for step: " + step.getName()
                    + "StartMax: " + step.getStartLimit());
        }
    }
    */
}
