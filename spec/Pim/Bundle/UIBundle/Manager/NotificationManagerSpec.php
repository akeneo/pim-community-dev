<?php

namespace spec\Pim\Bundle\UIBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UIBundle\Entity\NotificationEvent;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UIBundle\Entity\Notification;
use Pim\Bundle\UIBundle\Factory\NotificationFactory;

class NotificationManagerSpec extends ObjectBehavior
{
    function let(EntityManager $em, EntityRepository $repository, NotificationFactory $factory)
    {
        $this->beConstructedWith($em, $repository, $factory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\UIBundle\Manager\NotificationManager');
    }

    function it_can_create_a_notification(
        User $user,
        NotificationEvent $event,
        Notification $notification,
        $em,
        $factory
    ) {
        $factory
            ->createNotificationEvent('Some message', 'success', Argument::any())
            ->shouldBeCalled()
            ->willReturn($event);
        $factory->createNotification($event, $user)
            ->shouldBeCalled()
            ->willReturn($notification);
        $em->persist($event)->shouldBeCalled();
        $em->persist($notification)->shouldBeCalled();

        $this->notify([$user], 'Some message')->shouldReturn($this);
    }

    function it_can_create_multiple_notifications(
        User $user,
        User $user2,
        NotificationEvent $event,
        Notification $notification,
        $em,
        $factory
    ) {
        $factory
            ->createNotificationEvent('Some message', 'success', Argument::any())
            ->shouldBeCalled()
            ->willReturn($event);
        $factory->createNotification($event, Argument::type('Oro\Bundle\UserBundle\Entity\User'))
            ->shouldBeCalledTimes(2)
            ->willReturn($notification);

        $em->persist($event)->shouldBeCalled();
        $em->persist(Argument::type('Pim\Bundle\UIBundle\Entity\Notification'))->shouldBeCalledTimes(2);

        $this->notify([$user, $user2], 'Some message')->shouldReturn($this);
    }

    function it_can_return_all_notifications_for_a_user(Notification $notification, User $user, $repository)
    {
        $repository->findBy(['user' => $user])->willReturn([$notification]);
        $this->getNotifications($user)->shouldReturn([$notification]);
    }
}
