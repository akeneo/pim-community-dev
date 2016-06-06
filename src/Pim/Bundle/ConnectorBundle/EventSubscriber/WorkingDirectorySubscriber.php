<?php

namespace Pim\Bundle\ConnectorBundle\EventSubscriber;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Pim\Component\Connector\WorkingDirectory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkingDirectorySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'create',
            EventInterface::AFTER_JOB_EXECUTION => 'remove',
        ];
    }

    public function create(JobExecutionEvent $event)
    {
        $context = $event->getJobExecution()->getExecutionContext();
        $context->put('workingDirectory', new WorkingDirectory());
    }

    public function remove(JobExecutionEvent $event)
    {   
        $context = $event->getJobExecution()->getExecutionContext();

        if (null !== $workingDirectory = $context->get('workingDirectory')) {
            $workingDirectory->remove();
            $context->remove('workingDirectory');
        }
    }
}
