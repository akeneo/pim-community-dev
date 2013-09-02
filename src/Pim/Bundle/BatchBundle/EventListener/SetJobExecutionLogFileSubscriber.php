<?php

namespace Pim\Bundle\BatchBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Pim\Bundle\BatchBundle\Event\JobExecutionEvent;

/**
 * Set the job execution log file into the job execution instance
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
