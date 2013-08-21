<?php

namespace Pim\Bundle\BatchBundle\Step;

use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;
use Pim\Bundle\BatchBundle\Job\JobRepositoryInterface;
use Pim\Bundle\BatchBundle\Job\JobInterruptedException;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * A Step implementation that provides common behavior to subclasses, including registering and calling
 * listeners.
 *
 * Inspired by Spring Batch org.springframework.batch.core.step.AbstractStep;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractStep implements StepInterface
{
    private $name;

    private $logger;

    /* @var JobRepositoryInterace */
    private $jobRepository;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
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
     *
     * @return object
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * Public setter for {@link JobRepositoryInterface}.
     *
     * @param JobRepositoryInterface $jobRepository jobRepository is a mandatory dependence (no default).
     *
     * @return $this
     */
    public function setJobRepository(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;

        return $this;
    }

    /**
     * @return JobRepositoryInterface
     */
    public function getJobRepository()
    {
        return $this->jobRepository;
    }

    /**
     * {@inheritdoc}
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
     * @return this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * @param StepExecution $stepExecution the current step context
     *
     * @throws Exception
     */
    abstract protected function doExecute(StepExecution $stepExecution);

    /**
     * Provide the configuration of the step
     *
     * @return array
     */
    abstract public function getConfiguration();

    /**
     * Set the configuration for the step
     *
     * @param array $config
     */
    abstract public function setConfiguration(array $config);

    /**
     * Template method for step execution logic
     *
     * @param StepExecution $stepExecution
     *
     * @throws JobInterruptException
     * @throws UnexpectedJobExecutionException
     */
    final public function execute(StepExecution $stepExecution)
    {
        $this->getLogger()->debug("Executing: id=" . $stepExecution->getId());

        $stepExecution->setStartTime(new \DateTime());
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STARTED));

        $this->getJobRepository()->updateStepExecution($stepExecution);

        // Start with a default value that will be trumped by anything
        $exitStatus = new ExitStatus(ExitStatus::EXECUTING);

        try {
            $this->doExecute($stepExecution);

            $exitStatus = new ExitStatus(ExitStatus::COMPLETED);
            $exitStatus->logicalAnd($stepExecution->getExitStatus());
            // Check if someone is trying to stop us
            if ($stepExecution->isTerminateOnly()) {
                throw new JobInterruptedException("JobExecution interrupted.");
            }

            // Need to upgrade here not set, in case the execution was stopped
            $stepExecution->upgradeStatus(BatchStatus::COMPLETED);
            $this->getLogger()->debug("Step execution success: id=" . $stepExecution->getId());
        } catch (\Exception $e) {
            $stepExecution->upgradeStatus($this->determineBatchStatus($e));

            $exitStatus = $exitStatus->logicalAnd($this->getDefaultExitStatusForFailure($e));

            $stepExecution->addFailureException($e);

            if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
                $this->getLogger()->info("Encountered interruption executing step: " . $e->getMessage());
                $this->getLogger()->debug("Full exception", array('exception', $e));
            } else {
                $this->getLogger()->error("Encountered an error executing the step", array('exception' => $e));
            }
        }

        $stepExecution->setEndTime(new \DateTime());
        $stepExecution->setExitStatus($exitStatus);

        try {
            $this->getJobRepository()->updateStepExecution($stepExecution);
        } catch (\Exception $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::UNKNOWN));
            $stepExecution->setExitStatus($exitStatus->and(ExitStatus::UNKNOWN));
            $stepExecution->addFailureException($e);
            $errorMsg = "Encountered an error saving batch meta data. "
                . "This job is now in an unknown state.";
            $this->getLogger()->error($errorMsg, array('exception' => $e));
        }

        $this->getLogger()->debug("Step execution complete: " . $stepExecution->__toString());
    }

    /**
     * Determine the step status based on the exception.
     * @param Exception $e
     *
     * @return mixed
     */
    private static function determineBatchStatus(\Exception $e)
    {
        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            return BatchStatus::STOPPED;
        } else {
            return BatchStatus::FAILED;
        }
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
}
