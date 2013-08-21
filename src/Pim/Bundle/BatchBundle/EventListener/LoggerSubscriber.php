<?php

namespace Pim\Bundle\BatchBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Pim\Bundle\BatchBundle\Event\JobExecutionEvent;
use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\Event\StepEvent;

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
            EventInterface::BEFORE_JOB_EXECUTION      => 'beforeJobExecution',
            EventInterface::JOB_EXECUTION_STOPPED     => 'jobExecutionStopped',
            EventInterface::JOB_EXECUTION_INTERRUPTED => 'jobExecutionInterrupted',
            EventInterface::JOB_EXECUTION_FATAL_ERROR => 'jobExecutionFatalError',
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'beforeJobStatusUpgrade',
            EventInterface::BEFORE_STEP_EXECUTION     => 'beforeStepExecution',
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

    public function beforeStepExecution(StepEvent $event)
    {
        $step = $event->getStep();

        $this->logger->info(sprintf('Executing step: [%s]', $step->getName()));
    }
}
