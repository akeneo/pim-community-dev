<?php

namespace spec\Pim\Bundle\NotificationBundle\Twig;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;

class NotificationExtensionSpec extends ObjectBehavior
{
    function let(UserNotificationManager $manager, UserContext $context)
    {
        $this->beConstructedWith($manager, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Twig\NotificationExtension');
    }

    function it_counts_notifications(User $user, $context, $manager)
    {
        $context->getUser()->willReturn($user);
        $manager->countUnreadForUser($user)->willReturn(3);

        $this->countNotifications()->shouldReturn(3);
    }

    function it_returns_a_notification_count_equal_to_zero_if_it_has_no_user($context, $manager)
    {
        $context->getUser()->willReturn(null);
        $manager->countUnreadForUser(Argument::cetera())->shouldNotBeCalled();

        $this->countNotifications()->shouldReturn(0);
    }

    function it_gives_name()
    {
        $this->getName()->shouldReturn('pim_notification_extension');
    }
}
