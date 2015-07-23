<?php

namespace spec\Pim\Bundle\NotificationBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Manager\NotificationManagerInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationExtensionSpec extends ObjectBehavior
{
    function let(NotificationManagerInterface $manager, UserContext $context)
    {
        $this->beConstructedWith($manager, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Twig\NotificationExtension');
    }

    function it_provides_the_unread_notification_count(UserInterface $user, $context, $manager)
    {
        $context->getUser()->willReturn($user);
        $manager->countUnreadForUser($user)->willReturn(3);

        $this->countNotifications()->shouldReturn(3);
    }

    function it_returns_zero_if_no_user_is_present_in_the_context($manager)
    {
        $manager->countUnreadForUser(Argument::cetera())->shouldNotBeCalled();

        $this->countNotifications()->shouldReturn(0);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldBe('pim_notification_extension');
    }
}
