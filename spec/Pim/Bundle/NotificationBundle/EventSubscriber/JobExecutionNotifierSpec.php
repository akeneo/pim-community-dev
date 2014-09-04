<?php

namespace spec\Pim\Bundle\NotificationBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(UserNotificationManager $manager, UserContext $context)
    {
        $this->beConstructedWith($manager, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\EventSubscriber\JobExecutionNotifier');
    }

    function it_gives_the_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                'akeneo_batch.after_job_execution' => 'afterJobExecution'
            ]
        );
    }


}
