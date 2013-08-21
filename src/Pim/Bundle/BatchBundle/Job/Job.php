<?php

namespace Pim\Bundle\BatchBundle\Job;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\BatchBundle\Step\StepInterface;
use Pim\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Implementation of the {@link Job} interface.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.AbstractJob;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Job implements JobInterface
{
    protected $name;

    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @var array
     *
     * @Assert\Valid
     */
    protected $steps;

    protected $logger = null;

    /**
     * Convenience constructor to immediately add name (which is mandatory)
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name   = $name;
        $this->steps  = array();
    }

    /**
     * Set the logger
     * @param object $logger
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
     * Get the job's name
     *
     * @return name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name property
     *
     * @param string $name
     *
     * @return Job
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return all the steps
     *
     * @return array steps
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Public setter for the steps in this job. Overrides any calls to
     * addStep(Step).
     *
     * @param array $steps the steps to execute
     *
     * @return Job
     */
    public function setSteps(array $steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     *
     * @param string $stepName
     *
     * @return Step the Step
     */
    public function getStep($stepName)
    {
        foreach ($this->steps as $step) {
            if ($step->getName() == $stepName) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Retrieve the step names.
     *
     * @return array the step names
     */
    public function getStepNames()
    {
        $names = array();
        foreach ($this->steps as $step) {
            $names[] = $step->getName();
        }

        return $names;
    }

    /**
     * Convenience method for adding a single step to the job.
     *
     * @param StepInterface $step a {@link Step} to add
     */
    public function addStep(StepInterface $step)
    {
        $this->steps[] = $step;
    }

    /**
     * Public setter for the {@link JobRepositoryInterface} that is needed to manage the
     * state of the batch meta domain (jobs, steps, executions) during the life
     * of a job.
     *
     * @param JobRepositoryInterface $jobRepository
     */
    public function setJobRepository(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * Run the specified job, handling all listener and repository calls, and
     * delegating the actual processing to {@link #doExecute(JobExecution)}.
     * @param JobExecution $execution
     *
     * @see Job#execute(JobExecution)
     * @throws StartLimitExceededException
     *             if start limit of one of the steps was exceeded
     */
    final public function execute(JobExecution $jobExecution)
    {
        $this->getLogger()->debug("Job execution starting: " . $jobExecution);

        try {
            if ($jobExecution->getStatus()->getValue() !== BatchStatus::STOPPING) {

                $jobExecution->setStartTime(new \DateTime());
                $this->updateStatus($jobExecution, BatchStatus::STARTED);

                // Todo Listener beforeJob
                 $this->doExecute($jobExecution);
            } else {

                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $jobExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));
                $this->getLogger()->debug("Job execution was stopped: ". $jobExecution);

            }

        } catch (JobInterruptedException $e) {
            $this->getLogger()->info("Encountered interruption executing job: " . $e->getMessage());
            $this->getLogger()->debug("Full exception", array('exception', $e));

            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(
                new BatchStatusi(
                    BatchStatus::max(BatchStatus::STOPPED, $e->getStatus()->getValue())
                )
            );
            $jobExecution->addFailureException($e);
        } catch (\Exception $e) {
            $this->getLogger()->error("Encountered fatal error executing job", array('exception', $e));
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
            $jobExecution->addFailureException($e);
        }

        if (($jobExecution->getStatus()->getValue() <= BatchStatus::STOPPED)
                && (count($jobExecution->getStepExecutions()) == 0)
        ) {
            /* @var ExitStatus */
            $exitStatus = $jobExecution->getExitStatus();
            $noopExitStatus = new ExitStatus(ExitStatus::NOOP);
            $noopExitStatus->addExitDescription("All steps already completed or no steps configured for this job.");
            $jobExecution->setExitStatus($exitStatus->logicalAnd($noopExitStatus));
        }

        $jobExecution->setEndTime(new \DateTime());

        $this->jobRepository->updateJobExecution($jobExecution);
        $this->jobRepository->flush();
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param Exception $e the cause of the failure
     *
     * @return an {@link ExitStatus}
     */
    private function getDefaultExitStatusForFailure(\Exception $e)
    {
        $exitStatus = new ExitStatus();

        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            $exitStatus = new ExitStatus(ExitStatus::STOPPED);
            $exitStatus->addExitDescription(get_class(new JobInterruptedException()));
        } else {
            $exitStatus = new ExitStatus(ExitStatus::FAILED);
            $exitStatus->addExitDescription($e);
        }

        return $exitStatus;
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param JobExecution $jobExecution Execution of the job
     * @param string       $status       Status of the execution
     *
     * @return an {@link ExitStatus}
     */
    private function updateStatus(JobExecution $jobExecution, $status)
    {
        $jobExecution->setStatus(new BatchStatus($status));
        $this->jobRepository->updateJobExecution($jobExecution);
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . ': [name=' . $this->name . ']';
    }

    /**
     * Get the steps configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        $result = array();
        foreach ($this->steps as $step) {
            $result[$step->getName()] = $step->getConfiguration();
        }

        return $result;
    }

    /**
     * Set the steps configuration
     *
     * @param array $steps
     */
    public function setConfiguration(array $steps)
    {
        foreach ($steps as $title => $config) {
            $step = $this->getStep($title);
            if (!$step) {
                throw new \InvalidArgumentException(sprintf('Unknown step "%s"', $title));
            }

            $step->setConfiguration($config);
        }
    }

    /**
     * Handler of steps sequentially as provided, checking each one for success
     * before moving to the next. Returns the last {@link StepExecution}
     * successfully processed if it exists, and null if none were processed.
     *
     * @param JobExecution $jobExecution the current {@link JobExecution}
     *
     * @throws JobInterruptedException
     * @throws JobRestartException
     * @throws StartLimitExceededException
     */
    protected function doExecute(JobExecution $jobExecution)
    {
        /* @var StepExecution $stepExecution */
        $stepExecution = null;

        foreach ($this->steps as $step) {
            $stepExecution = $this->handleStep($step, $jobExecution);

            if ($stepExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
                // Terminate the job if a step fails
                break;
            }
        }

        // Update the job status to be the same as the last step
        if ($stepExecution !== null) {
            $this->getLogger()->debug("Upgrading JobExecution status: " . $stepExecution);
            $jobExecution->upgradeStatus($stepExecution->getStatus()->getValue());
            $jobExecution->setExitStatus($stepExecution->getExitStatus());
        }
    }

    /**
     * Handle a step and return the execution for it.
     * @param StepInterface $step         Step
     * @param JobExecution  $jobExecution Job execution
     *
     * @throws JobInterruptedException
     * @throws JobRestartException
     * @throws StartLimitExceededException
     *
     * @return StepExecution
     */
    public function handleStep(StepInterface $step, JobExecution $jobExecution)
    {
        if ($jobExecution->isStopping()) {
            throw new JobInterruptedException("JobExecution interrupted.");
        }

        $stepExecution = $jobExecution->createStepExecution($step->getName());

        $this->getLogger()->info("Executing step: [" . $step->getName() . "]");
        try {
            $step->execute($stepExecution);
        } catch (JobInterruptedException $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw $e;
        }

        if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPING
                || $stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
            $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            throw new JobInterruptedException("Job interrupted by step execution");
        }

        return $stepExecution;
    }
}
