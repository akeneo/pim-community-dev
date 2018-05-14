<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Set the job execution log file into the job execution instance
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'setJobExecutionLogFile',
        ];
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
