<?php

namespace spec\Pim\Bundle\NotificationBundle\Manager;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class NotificationManagerSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        UserNotificationRepositoryInterface $repository,
        NotificationFactoryInterface $notificationFactory,
        UserProviderInterface $userProvider,
        UserNotificationFactoryInterface $userNotificationFactory
    ) {
        $this->beConstructedWith($em, $repository, $notificationFactory, $userNotificationFactory, $userProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Manager\NotificationManager');
    }

    function it_can_create_a_notification(
        UserInterface $user,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
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
        $em->flush($notification)->shouldBeCalled();
        $em->flush([$userNotification])->shouldBeCalled();

        $this->notify([$user], 'Some message');
    }

    function it_can_create_multiple_notifications(
        UserInterface $user,
        UserInterface $user2,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        $em,
        $notificationFactory,
        $userNotificationFactory
    ) {
        $notificationFactory
            ->createNotification('Some message', 'success', Argument::any())
            ->willReturn($notification);
        $userNotificationFactory
            ->createUserNotification(
                $notification,
                Argument::type('Symfony\Component\Security\Core\User\UserInterface')
            )
            ->willReturn($userNotification);

        $em->persist($notification)->shouldBeCalled();
        $em->persist(Argument::type('Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface'))->shouldBeCalledTimes(2);
        $em->flush($notification)->shouldBeCalled();
        $em->flush([$userNotification, $userNotification])->shouldBeCalled();

        $this->notify([$user, $user2], 'Some message');
    }

    function it_can_return_all_notifications_for_a_user(
        UserNotificationInterface $userNotification,
        UserInterface $user,
        $repository
    ) {
        $repository->findBy(['user' => $user], ['id' => 'DESC'], 10, 15)->willReturn([$userNotification]);
        $this->getUserNotifications($user, 15)->shouldReturn([$userNotification]);
    }

    function it_can_mark_a_notification_as_viewed(UserInterface $user, $repository)
    {
        $repository->markAsViewed($user, 1)->shouldBeCalled();

        $this->markAsViewed($user, 1);
    }

    function it_can_mark_all_notifications_as_viewed(UserInterface $user, $repository)
    {
        $repository->markAsViewed($user, 'all')->shouldBeCalled();

        $this->markAsViewed($user, 'all');
    }

    function it_can_remove_a_notification(UserNotificationInterface $userNotification, UserInterface $user, $repository, $em)
    {
        $repository->findOneBy(['id' => 1, 'user' => $user])->willReturn($userNotification);

        $em->remove($userNotification)->shouldBeCalled();
        $em->flush($userNotification)->shouldBeCalled();

        $this->remove($user, 1);
    }
}
