<?php

namespace Pim\Bundle\BatchBundle\Job;

use Symfony\Component\Validator\Constraints as Assert;

use Pim\Bundle\BatchBundle\Job\AbstractJob;
use Pim\Bundle\BatchBundle\Job\JobExecution;
use Pim\Bundle\BatchBundle\Step\StepInterface;

/**
 * Simple implementation of {@link Job} interface providing the ability to run a
 * {@link JobExecution}. Sequentially executes a job by iterating through its
 * list of steps.  Any {@link Step} that fails will fail the job.  The job is
 * considered complete when all steps have been executed.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.SimpleJob
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleJob extends AbstractJob
{
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
            $stepExecution = $this->handleStep($step, $execution);
            if ($stepExecution->getStatus()->getValue() != BatchStatus::COMPLETED) {
                //
                // Terminate the job if a step fails
                //
                break;
            }
        }

        //
        // Update the job status to be the same as the last step
        //
        if ($stepExecution != null) {
            $this->logger->debug("Upgrading JobExecution status: " . $stepExecution);
            $execution->upgradeStatus($stepExecution->getStatus()->getValue());
            $execution->setExitStatus($stepExecution->getExitStatus());
        }
    }

    public function getConfiguration()
    {
        $result = array();
        foreach ($this->steps as $step) {
            $result[$step->getName()] = $this->getStepConfiguration($step);
        }

        return $result;
    }

    private function getStepConfiguration($step)
    {
        return array(
            'reader'    => $step->getReader()->getConfiguration(),
            'processor' => $step->getProcessor()->getConfiguration(),
            'writer'    => $step->getWriter()->getConfiguration(),
        );
    }
}
