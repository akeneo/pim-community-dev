<?php

namespace Pim\Bundle\InstallerBundle\Exception;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Entity\Warning;
use Symfony\Component\Yaml\Yaml;

/**
 * Thrown when fixture import profiles fail
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionException extends \Exception
{
    /**
     * @param JobExecution $jobExecution
     */
    public function __construct(JobExecution $jobExecution)
    {
        $message = '';
        foreach ($jobExecution->getFailureExceptions() as $exception) {
            $message .= $this->getFailureExceptionMessage('JOB', $exception);
        }

        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            $message .= $this->getStepExecutionMessages($stepExecution);
        }

        parent::__construct($message);
    }

    /**
     * Returns the messages for a step execution
     *
     * @param StepExecution $stepExecution
     *
     * @return string
     */
    protected function getStepExecutionMessages(StepExecution $stepExecution)
    {
        $message = '';

        foreach ($stepExecution->getFailureExceptions() as $exception) {
            $message .= $this->getFailureExceptionMessage(
                sprintf('STEP %s', $stepExecution->getStepName()),
                $exception
            );
        }

        foreach ($stepExecution->getWarnings() as $warning) {
            $message .= $this->getWarningMessage($warning);
        }

        return $message;
    }

    /**
     * Returns message for a failure exception
     *
     * @param string $type
     * @param array  $exception
     *
     * @return string
     */
    protected function getFailureExceptionMessage($type, $exception)
    {
        return sprintf(
            "%s FAILURE: %s (%s)\n%s\n",
            $type,
            $exception['class'],
            $exception['code'],
            strtr($exception['message'], $exception['messageParameters']),
            $exception['trace']
        );
    }

    /**
     * Returns a warning message
     *
     * @param Warning $warning
     *
     * @return string
     */
    protected function getWarningMessage(Warning $warning)
    {
        return sprintf(
            "STEP %s WARNING on item %s:\n%s\n",
            $warning->getStepExecution()->getStepName(),
            Yaml::dump($warning->getItem(), 0),
            strtr($warning->getReason(), $warning->getReasonParameters())
        );
    }
}
