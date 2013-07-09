<?php
                                                                                
namespace Pim\Bundle\BatchBundle\Job;

use Pim\Bundle\BatchBundle\Step\StepInterface;

use Pim\Bundle\BatchBundle\Logger;

/**
 * 
 * Abstract implementation of the {@link Job} interface. Common dependencies
 * such as a {@link JobRepository}, {@link JobExecutionListener}s, and various
 * configuration parameters are set here. Therefore, common error handling and
 * listener calling activities are abstracted away from implementations.

 * Inspired by Spring Batch org.springframework.batch.core.job.AbstractJob;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbstractJob implements JobInterface
{

    private $name;

//    private boolean restartable = true;
//    private CompositeStepExecutionListener stepExecutionListener = new CompositeStepExecutionListener();
//    private JobRepository jobRepository;
//    private JobParametersIncrementer jobParametersIncrementer;
//    private JobParametersValidator jobParametersValidator = new DefaultJobParametersValidator();

    /* @var StepHandler $stepHandler */
    private $stepHandler = null;

    /**
     * Convenience constructor to immediately add name (which is mandatory)
     *
     * @param name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name property
     *
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     *
     * @param stepName
     * @return Step the Step
     */
    public abstract function getStep($stepName);

    /**
     * Retrieve the step names.
     *
     * @return array the step names
     */
    public abstract function getStepNames();

    /**
     * Public setter for the {@link JobRepository} that is needed to manage the
     * state of the batch meta domain (jobs, steps, executions) during the life
     * of a job.
     *
     * @param jobRepository
     */
    public function setJobRepository(JobRepository $jobRepository) {
        $this->jobRepository = $jobRepository;
        $this->stepHandler = new SimpleStepHandler($jobRepository);
    }

    /**
     * Convenience method for subclasses to access the job repository.
     *
     * @return the jobRepository
     */
    protected function getJobRepository() {
        return $this->jobRepository;
    }


    /**
     * Extension point for subclasses allowing them to concentrate on processing
     * logic and ignore listeners and repository calls. Implementations usually
     * are concerned with the ordering of steps, and delegate actual step
     * processing to {@link #handleStep(Step, JobExecution)}.
     *
     * @param execution
     *            the current {@link JobExecution}
     *
     * @throws JobExecutionException
     *             to signal a fatal batch framework error (not a business or
     *             validation exception)
     */
    abstract protected function doExecute(JobExecution $execution);

    /**
     * Run the specified job, handling all listener and repository calls, and
     * delegating the actual processing to {@link #doExecute(JobExecution)}.
     *
     * @see Job#execute(JobExecution)
     * @throws StartLimitExceededException
     *             if start limit of one of the steps was exceeded
     */
    public final function execute(JobExecution $execution) {

        Logger::debug("Job execution starting: " . $execution);

        try {
//            jobParametersValidator.validate(execution.getJobParameters());

            if ($execution->getStatus()->getValue() != BatchStatus::STOPPING) {

                $execution->setStartTime(time());
                $this->updateStatus($execution, BatchStatus::STARTED);

//                listener.beforeJob(execution);

//                try {
                    $this->doExecute($execution);
                    Logger::debug("Job execution complete: ". $execution);
//                } catch (RepeatException e) {
//                    throw e.getCause();
//                }
            } else {

                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $execution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $execution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));
                Logger::debug("Job execution was stopped: ". $execution);

            }


        } catch (JobInterruptedException $e) {
            Logger::info("Encountered interruption executing job: " . $e->getMessage());
            Logger::debug("Full exception", $e);

            $execution->setExitStatus($this->getDefaultExitStatusForFailure($e));
//            $execution->setStatus(new BatchStatus(BatchStatus::max(BatchStatus::STOPPED, e.getStatus()->getValue())));
            $execution->addFailureException($e);
        } catch (\Exception $e) {
            Logger::error("Encountered fatal error executing job", $e);
            $execution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $execution->setStatus(new BatchStatus(BatchStatus::FAILED));
//            $execution->addFailureException($e);
        } 

        if ( ($execution->getStatus()->getValue() <= BatchStatus::STOPPED)
                && $execution->getStepExecutions()->isEmpty()
        ) {
            /* @var ExitStatus $exitStatus */
            $exitStatus = $execution->getExitStatus();
            $noopExitStatus = new ExitStatus(ExitStatus::NOOP);
            $noopExitStatus->addExitDescription("All steps already completed or no steps configured for this job.");
            $execution->setExitStatus($exitStatus->logicalAnd($noopExitStatus));
        }

        $execution->setEndTime(time());

        try {
//            listener.afterJob(execution);
        } catch (Exception $e) {
            Logger::error("Exception encountered in afterStep callback", $e);
        }

//        jobRepository.update(execution);
    }


    /**
     * Convenience method for subclasses to delegate the handling of a specific
     * step in the context of the current {@link JobExecution}. Clients of this
     * method do not need access to the {@link JobRepository}, nor do they need
     * to worry about populating the execution context on a restart, nor
     * detecting the interrupted state (in job or step execution).
     *
     * @param step
     *            the {@link Step} to execute
     * @param execution
     *            the current {@link JobExecution}
     * @return the {@link StepExecution} corresponding to this step
     *
     * @throws JobInterruptedException
     *             if the {@link JobExecution} has been interrupted, and in
     *             particular if {@link BatchStatus#ABANDONED} or
     *             {@link BatchStatus#STOPPING} is detected
     * @throws StartLimitExceededException
     *             if the start limit has been exceeded for this step
     * @throws JobRestartException
     *             if the job is in an inconsistent state from an earlier
     *             failure
     */
    protected function handleStep(StepInterface $step, JobExecution $execution)
    {
        return $this->stepHandler->handleStep($step, $execution);
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param ex the cause of the failure
     * @return an {@link ExitStatus}
     */
    private function getDefaultExitStatusForFailure(\Exception $e)
    {
        $exitStatus = new ExitStatus();

        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            $exitStatus = new ExitStatus(ExitStatus::STOPPED);
            $exitStatus->addExitDescription(get_class(JobInterruptedException));
        }
        else if ($e instanceof NoSuchJobException || $e->getPrevious() instanceof NoSuchJobException) {
//            exitStatus = new ExitStatus(ExitCodeMapper.NO_SUCH_JOB, ex.getClass().getName());
        }
        else {
            $exitStatus = new ExitStatus(ExitStatus::FAILED);
            $exitStatus->addExitDescription($e);
        }

        return $exitStatus;
    }


    private function updateStatus(JobExecution $jobExecution, $status) {
        $jobExecution->setStatus(new BatchStatus($status));
//        $jobRepository->update($jobExecution);
    }

    public function __toString() {
        return get_class($this) . ': [name=' . $this->name . ']';
    }







}
