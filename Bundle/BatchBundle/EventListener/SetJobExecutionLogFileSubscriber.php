<?php

namespace Oro\Bundle\BatchBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;

/**
 * Set the job execution log file into the job execution instance
 *
 */
class SetJobExecutionLogFileSubscriber implements EventSubscriberInterface
{
    /**
     * @var BatchLogHandler $logger
     */
    protected $logger;

    /**
     * @param BatchLogHandler $logger
     */
    public function __construct(BatchLogHandler $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_JOB_EXECUTION => 'setJobExecutionLogFile',
        );
    }

    /**
     * Set the job execution log file
     *
     * @param JobExecutionEvent $event
     */
    public function setJobExecutionLogFile(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $jobExecution->setLogFile(
            $this->logger->getFilename()
        );
    }
}
