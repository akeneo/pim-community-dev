<?php

namespace spec\Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
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
