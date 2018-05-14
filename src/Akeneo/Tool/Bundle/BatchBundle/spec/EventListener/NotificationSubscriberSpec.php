<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class NotificationSubscriberSpec extends ObjectBehavior
{
    function it_notifies_notifier(
        Notifier $notifier1,
        Notifier $notifier2,
        JobExecutionEvent $event,
        JobExecution $execution
    ) {
        $this->registerNotifier($notifier1);
        $this->registerNotifier($notifier2);
        $event->getJobExecution()->willReturn($execution);
        $notifier1->notify($execution)->shouldBeCalled();
        $notifier2->notify($execution)->shouldBeCalled();

        $this->afterJobExecution($event);
    }
}
