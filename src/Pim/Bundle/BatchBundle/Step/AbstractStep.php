<?php

namespace Pim\Bundle\BatchBundle\Step;

use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

use Pim\Bundle\BatchBundle\Item\ExecutionContext;

use Pim\Bundle\BatchBundle\Logger;

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

    //private CompositeStepExecutionListener stepExecutionListener = new CompositeStepExecutionListener();

    //private JobRepository jobRepository;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name property
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning. TODO
     *
     * @param StepExecution $stepExecution the current step context
     *
     * @throws Exception
     */
    abstract protected function doExecute(StepExecution $stepExecution);

    /**
     * Extension point for subclasses to provide callbacks to their collaborators at the beginning of a step, to open or
     * acquire resources. Does nothing by default.
     *
     * @param ExecutionContext $ctx the {@link ExecutionContext} to use
     *
     * @throws Exception
     */
    protected function open(ExecutionContext $ctx)
    {
    }

    /**
     * Extension point for subclasses to provide callbacks to their collaborators at the end of a step (right at the end
     * of the finally block), to close or release resources. Does nothing by default.
     *
     * @param ExecutionContext $ctx the {@link ExecutionContext} to use
     *
     * @throws Exception
     */
    protected function close(ExecutionContext $ctx)
    {
    }

    /**
     * Template method for step execution logic - calls abstract methods for resource initialization (
     * {@link #open(ExecutionContext)}), execution logic ({@link #doExecute(StepExecution)}) and resource closing (
     * {@link #close(ExecutionContext)}).
     *
     * @param StepExecution $stepExecution
     *
     * @throws JobInterruptException
     * @throws UnexpectedJobExecutionException
     */
    final public function execute(StepExecution $stepExecution)
    {
        Logger::debug("Executing: id=" . $stepExecution->getId());

        $stepExecution->setStartTime(time());
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STARTED));

        //FIXME: persist stepExecution getJobRepository().update(stepExecution);

        // Start with a default value that will be trumped by anything
        $exitStatus = new ExitStatus(ExitStatus::EXECUTING);

        //StepSynchronizationManager.register(stepExecution);

        try {
            //getCompositeListener().beforeStep(stepExecution);
            //$this->open($stepExecution->getExecutionContext());

            $this->doExecute($stepExecution);

            $exitStatus = new ExitStatus(ExitStatus::COMPLETED);
            $exitStatus->logicalAnd($stepExecution->getExitStatus());

            // Check if someone is trying to stop us
            if ($stepExecution->isTerminateOnly()) {
                throw new JobInterruptedException("JobExecution interrupted.");
            }

            // Need to upgrade here not set, in case the execution was stopped
            $stepExecution->upgradeStatus(BatchStatus::COMPLETED);
            Logger::debug("Step execution success: id=" . $stepExecution->getId());
        } catch (\Exception $e) {
            $stepExecution->upgradeStatus($this->determineBatchStatus($e));

            $exitStatus = $exitStatus->logicalAnd($this->getDefaultExitStatusForFailure($e));

            $stepExecution->addFailureException($e);

            if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
                Logger::info("Encountered interruption executing step: " . $e->getMessage());
                Logger::debug("Full exception", $e);
            } else {
                Logger::error("Encountered an error executing the step", $e);
            }
        }

        try {
            // Update the step execution to the latest known value so the
            // listeners can act on it
            $exitStatus = $exitStatus->logicalAnd($stepExecution->getExitStatus());
            $stepExecution->setExitStatus($exitStatus);
            //$exitStatus = $exitStatus->and($this->getCompositeListener()->afterStep($stepExecution));
        } catch (Exception $e) {
            Logger::error("Exception in afterStep callback", $e);
        }

        try {
            //getJobRepository().updateExecutionContext(stepExecution);
        } catch (Exception $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::UNKNOWN));
            $exitStatus = $exitStatus->and(ExitStatus::UNKNOWN);
            $stepExecution->addFailureException($e);
            $errorMsg =  "Encountered an error saving batch meta data."
                ."This job is now in an unknown state and should not be restarted.";
            Logger::error($errorMsg, $e);
        }

        $stepExecution->setEndTime(time());
        $stepExecution->setExitStatus($exitStatus);

        try {
            //getJobRepository().update(stepExecution);
        } catch (Exception $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::UNKNOWN));
            $stepExecution->setExitStatus($exitStatus->and(ExitStatus::UNKNOWN));
            $stepExecution->addFailureException($e);
            $errorMsg = "Encountered an error saving batch meta data. "
                . "This job is now in an unknown state and should not be restarted.";
            Logger::error($errorMsg, $e);
        }

        try {
            $this->close($stepExecution->getExecutionContext());
        } catch (Exception $e) {
            Logger::error("Exception while closing step execution resources", $e);
            // $stepExecution.addFailureException(e);
        }

        //StepSynchronizationManager.release();

        Logger::debug("Step execution complete: " . $stepExecution->getSummary());
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
            /*} else if (ex instanceof NoSuchJobException || ex.getCause() instanceof NoSuchJobException) {
                exitStatus = new ExitStatus(ExitCodeMapper.NO_SUCH_JOB, ex.getClass().getName());*/
        } else {
            $exitStatus = new ExitStatus(ExitStatus::FAILED);
            $exitStatus->addExitDescription($e);
        }

        return $exitStatus;
    }
}
