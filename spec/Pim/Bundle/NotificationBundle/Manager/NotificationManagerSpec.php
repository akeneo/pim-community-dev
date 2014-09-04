<?php

namespace spec\Pim\Bundle\NotificationBundle\Manager;

use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\NotificationBundle\Entity\Notification;
use Pim\Bundle\NotificationBundle\Entity\NotificationEvent;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactory;

class NotificationManagerSpec extends ObjectBehavior
{
    function let(EntityManager $em, EntityRepository $repository, NotificationFactory $factory, UserManager $userManager)
    {
        $this->beConstructedWith($em, $repository, $factory, $userManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Manager\NotificationManager');
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
        $em->flush()->shouldBeCalled();

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
        $em->persist(Argument::type('Pim\Bundle\NotificationBundle\Entity\Notification'))->shouldBeCalledTimes(2);
        $em->flush()->shouldBeCalled();

        $this->notify([$user, $user2], 'Some message')->shouldReturn($this);
    }

    function it_can_return_all_notifications_for_a_user(Notification $notification, User $user, $repository)
    {
        $repository->findBy(['user' => $user])->willReturn([$notification]);
        $this->getNotifications($user)->shouldReturn([$notification]);
    }

    function it_marks_a_notification_as_viewed(Notification $notification, $repository, $em)
    {
        $userId = '2';
        $notificationId = '1';
        $repository->findBy(['user' => $userId, 'id' => $notificationId])->shouldBeCalled()->willReturn([$notification]);
        $notification->setViewed(true)->shouldBeCalled()->willReturn($notification);

        $em->persist($notification)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->markNotificationsAsViewed($userId, $notificationId);
    }

    function it_marks_all_notifications_as_viewed(
        Notification $notification1,
        Notification $notification2,
        $repository,
        $em
    ) {
        $userId = '2';
        $repository
            ->findBy(['user' => $userId, 'viewed' => false])
            ->shouldBeCalled()
            ->willReturn([$notification1, $notification2]);

        $notification1->setViewed(true)->shouldBeCalled()->willReturn($notification1);
        $notification2->setViewed(true)->shouldBeCalled()->willReturn($notification2);

        $em->persist($notification1)->shouldBeCalled();
        $em->persist($notification2)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->markNotificationsAsViewed($userId, 'all');
    }
}
