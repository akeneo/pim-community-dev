<?php

namespace spec\Pim\Bundle\NotificationBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class NotificationManagerSpec extends ObjectBehavior
{
    function let(
        UserNotificationRepositoryInterface $repository,
        NotificationFactoryInterface $notificationFactory,
        UserNotificationFactory $userNotificationFactory,
        UserProviderInterface $userProvider,
        UserNotificationFactory $userNotificationFactory,
        SaverInterface $notifSaver,
        BulkSaverInterface $userNotifsSaver,
        RemoverInterface $userNotifRemover,
        UserNotificationFactoryInterface $userNotificationFactory
    ) {
        $this->beConstructedWith(
            $repository,
            $notificationFactory,
            $userNotificationFactory,
            $userProvider,
            $notifSaver,
            $userNotifsSaver,
            $userNotifRemover
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Manager\NotificationManager');
    }

    function it_can_create_a_notification(
        UserInterface $user,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        $notificationFactory,
        $userNotificationFactory,
        $notifSaver,
        $userNotifsSaver
    ) {
        $notificationFactory
            ->createNotification('Some message', 'success', Argument::any())
            ->shouldBeCalled()
            ->willReturn($notification);
        $userNotificationFactory
            ->createUserNotification($notification, $user)
            ->shouldBeCalled()
            ->willReturn($userNotification);

        $notifSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->notify([$user], 'Some message');
    }

    function it_can_create_multiple_notifications(
        UserInterface $user,
        UserInterface $user2,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        $notificationFactory,
        $userNotificationFactory,
        $notifSaver,
        $userNotifsSaver
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

        $notifSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification, $userNotification])->shouldBeCalled();

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

    function it_can_remove_a_notification(
        UserNotificationInterface $userNotification,
        UserInterface $user,
        $repository,
        $userNotifRemover
    ) {
        $repository->findOneBy(['id' => 1, 'user' => $user])->willReturn($userNotification);
        $userNotifRemover->remove($userNotification)->shouldBeCalled();

        $this->remove($user, 1);
    }
}
