<?php

namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Step\StepInterface;
use Pim\Bundle\BatchBundle\Entity\JobExecution;

use Symfony\Component\Validator\Constraints as Assert;


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

    //private CompositeStepExecutionListener stepExecutionListener = new CompositeStepExecutionListener();

    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /* @var StepHandler */
    protected $stepHandler;

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
     * Set the step handler
     *
     * @param SimpleStepHandler $stepHandler
     */
    public function setStepHandler(SimpleStepHandler $stepHandler)
    {
        $this->stepHandler = $stepHandler;
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
    final public function execute(JobExecution $execution)
    {
        $this->getLogger()->debug("Job execution starting: " . $execution);

        try {
            //jobParametersValidator.validate(execution.getJobParameters());

            if ($execution->getStatus()->getValue() !== BatchStatus::STOPPING) {

                $execution->setStartTime(time());
                $this->updateStatus($execution, BatchStatus::STARTED);

                //listener.beforeJob(execution);
                 $this->doExecute($execution);
            } else {

                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $execution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $execution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));
                $this->getLogger()->debug("Job execution was stopped: ". $execution);

            }


        } catch (JobInterruptedException $e) {
            $this->getLogger()->info("Encountered interruption executing job: " . $e->getMessage());
            $this->getLogger()->debug("Full exception", array('exception', $e));

            $execution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $execution->setStatus(new BatchStatus(BatchStatus::max(BatchStatus::STOPPED, $e->getStatus()->getValue())));
            $execution->addFailureException($e);
        } catch (\Exception $e) {
            $this->getLogger()->error("Encountered fatal error executing job", array('exception', $e));
            $execution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $execution->setStatus(new BatchStatus(BatchStatus::FAILED));
            $execution->addFailureException($e);
        }

        if (($execution->getStatus()->getValue() <= BatchStatus::STOPPED)
                && (count($execution->getStepExecutions()) == 0)
        ) {
            /* @var ExitStatus */
            $exitStatus = $execution->getExitStatus();
            $noopExitStatus = new ExitStatus(ExitStatus::NOOP);
            $noopExitStatus->addExitDescription("All steps already completed or no steps configured for this job.");
            $execution->setExitStatus($exitStatus->logicalAnd($noopExitStatus));
        }

        $execution->setEndTime(time());

        /*
        try {
            $listener->afterJob($execution);
        } catch (Exception $e) {
            $this->logger->error("Exception encountered in afterStep callback", array('exception', $e));
        }
        */

        $this->jobRepository->updateJobExecution($execution);
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
     * @param JobExecution $execution the current {@link JobExecution}
     *
     * @see AbstractJob#handleStep(Step, JobExecution)
     * @throws JobInterruptedException
     * @throws JobRestartException
     * @throws StartLimitExceededException
     */
    protected function doExecute(JobExecution $execution)
    {
        /* @var StepExecution $stepExecution */
        $stepExecution = null;

        foreach ($this->steps as $step) {
            $stepExecution = $this->stepHandler->handleStep($step, $execution);
            if ($stepExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
                //
                // Terminate the job if a step fails
                //
                break;
            }
        }

        //
        // Update the job status to be the same as the last step
        //
        if ($stepExecution !== null) {
            $this->getLogger()->debug("Upgrading JobExecution status: " . $stepExecution);
            $execution->upgradeStatus($stepExecution->getStatus()->getValue());
            $execution->setExitStatus($stepExecution->getExitStatus());
        }
    }
}
