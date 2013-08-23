<?php

namespace Pim\Bundle\BatchBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Pim\Bundle\BatchBundle\Event\JobExecutionEvent;
use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\Event\StepEvent;
use Pim\Bundle\BatchBundle\Event\StepExecutionEvent;

/**
 * Subscriber to log job execution result
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoggerSubscriber implements EventSubscriberInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_JOB_EXECUTION       => 'beforeJobExecution',
            EventInterface::JOB_EXECUTION_STOPPED      => 'jobExecutionStopped',
            EventInterface::JOB_EXECUTION_INTERRUPTED  => 'jobExecutionInterrupted',
            EventInterface::JOB_EXECUTION_FATAL_ERROR  => 'jobExecutionFatalError',
            EventInterface::BEFORE_JOB_STATUS_UPGRADE  => 'beforeJobStatusUpgrade',
            EventInterface::BEFORE_STEP_EXECUTION      => 'beforeStepExecution',
            EventInterface::STEP_EXECUTION_SUCCEEDED   => 'stepExecutionSucceeded',
            EventInterface::STEP_EXECUTION_INTERRUPTED => 'stepExecutionInterrupted',
            EventInterface::STEP_EXECUTION_ERROR       => 'stepExecutionError',
        );
    }

    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution starting: %s', $jobExecution));
    }

    public function jobExecutionStopped(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution was stopped: %s', $jobExecution));
    }

    public function jobExecutionInterrupted(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->info(sprintf('Encountered interruption executing job: %s', $jobExecution));
        $this->logger->debug('Full exception', array('exception', $jobExecution->getFailureExceptions()));
    }

    public function jobExecutionFatalError(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->error(
            'Encountered fatal error executing job',
            array('exception', $jobExecution->getFailureExceptions())
        );
    }

    public function beforeJobStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Upgrading JobExecution status: %s', $jobExecution));
    }

    public function beforeStepExecution(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution starting: %s', $stepExecution));
    }

    public function stepExecutionSucceeded(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->debug(sprintf('Step execution success: id= %d', $stepExecution->getId()));
    }

    public function stepExecutionInterrupted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(
            sprintf('Encountered interruption executing step: %s', $stepExecution->getFailureExceptionMessages())
        );
        $this->logger->debug('Full exception', array('exception', $stepExecution->getFailureExceptions()));
    }

    public function stepExecutionError(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->error(
            sprintf('Encountered an error executing the step: %s', $stepExecution->getFailureExceptionMessages())
        );
    }
}
