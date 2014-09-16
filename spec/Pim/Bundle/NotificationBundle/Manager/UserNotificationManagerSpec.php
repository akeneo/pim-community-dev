<?php

namespace spec\Pim\Bundle\NotificationBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\Notification;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactory;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Prophecy\Argument;

class UserNotificationManagerSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        UserNotificationRepository $repository,
        NotificationFactory $notificationFactory,
        UserManager $userManager,
        UserNotificationFactory $userNotificationFactory
    ) {
        $this->beConstructedWith($em, $repository, $notificationFactory, $userNotificationFactory, $userManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Manager\UserNotificationManager');
    }

    function it_can_create_a_notification(
        User $user,
        Notification $notification,
        UserNotification $userNotification,
        $em,
        $notificationFactory,
        $userNotificationFactory
    ) {
        $notificationFactory
            ->createNotification('Some message', 'success', Argument::any())
            ->shouldBeCalled()
            ->willReturn($notification);
        $userNotificationFactory
            ->createUserNotification($notification, $user)
            ->shouldBeCalled()
            ->willReturn($userNotification);
        $em->persist($notification)->shouldBeCalled();
        $em->persist($userNotification)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->notify([$user], 'Some message')->shouldReturn($this);
    }

    function it_can_create_multiple_notifications(
        User $user,
        User $user2,
        Notification $notification,
        UserNotification $userNotification,
        $em,
        $notificationFactory,
        $userNotificationFactory
    ) {
        $notificationFactory
            ->createNotification('Some message', 'success', Argument::any())
            ->willReturn($notification);
        $userNotificationFactory
            ->createUserNotification($notification, Argument::type('Oro\Bundle\UserBundle\Entity\User'))
            ->willReturn($userNotification);

        $em->persist($notification)->shouldBeCalled();
        $em->persist(Argument::type('Pim\Bundle\NotificationBundle\Entity\UserNotification'))->shouldBeCalledTimes(2);
        $em->flush()->shouldBeCalled();

        $this->notify([$user, $user2], 'Some message')->shouldReturn($this);
    }

    function it_can_return_all_notifications_for_a_user(UserNotification $userNotification, User $user, $repository)
    {
        $repository->findBy(['user' => $user], ['id' => 'DESC'], 10, 15)->willReturn([$userNotification]);
        $this->getUserNotifications($user, 15)->shouldReturn([$userNotification]);
    }

    function it_can_mark_a_notification_as_viewed(User $user, $repository)
    {
        $repository->markAsViewed($user, 1)->shouldBeCalled();

        $this->markAsViewed($user, 1);
    }

    function it_can_mark_all_notifications_as_viewed(User $user, $repository)
    {
        $repository->markAsViewed($user, 'all')->shouldBeCalled();

        $this->markAsViewed($user, 'all');
    }

    function it_can_remove_a_notification(UserNotification $userNotification, User $user, $repository, $em)
    {
        $repository->findOneBy(['id' => 1, 'user' => $user])->willReturn($userNotification);

        $em->remove($userNotification)->shouldBeCalled();
        $em->flush($userNotification)->shouldBeCalled();

        $this->remove($user, 1);
    }
}
