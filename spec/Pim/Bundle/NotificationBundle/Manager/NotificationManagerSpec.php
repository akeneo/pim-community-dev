<?php

namespace spec\Pim\Bundle\NotificationBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotification;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class NotificationManagerSpec extends ObjectBehavior
{
    function let(
        UserNotificationRepositoryInterface $userNotifRepository,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        RemoverInterface $userNotifRemover,
        NotifierInterface $notifier
    ) {
        $this->beConstructedWith(
            $userNotifRepository,
            $userNotifFactory,
            $userProvider,
            $notificationSaver,
            $userNotifsSaver,
            $userNotifRemover,
            $notifier
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Manager\NotificationManager');
    }

    function it_can_create_a_notification(
        UserInterface $user,
        NotificationInterface $notification,
        $notifier
    ) {
        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->notify($notification, [$user]);
    }

    function it_can_create_multiple_notifications(
        $notifier,
        UserInterface $user,
        UserInterface $user2,
        NotificationInterface $notification
    ) {
        $notifier->notify($notification, [$user, $user2])->shouldBeCalled();

        $this->notify($notification, [$user, $user2]);
    }

    function it_can_return_all_notifications_for_a_user(
        $userNotifRepository,
        UserNotification $userNotification,
        UserInterface $user
    ) {
        $userNotifRepository->findBy(['user' => $user], ['id' => 'DESC'], 10, 15)->willReturn([$userNotification]);
        $this->getUserNotifications($user, 15)->shouldReturn([$userNotification]);
    }

    function it_can_mark_a_notification_as_viewed($userNotifRepository, UserInterface $user)
    {
        $userNotifRepository->markAsViewed($user, 1)->shouldBeCalled();

        $this->markAsViewed($user, 1);
    }

    function it_can_mark_all_notifications_as_viewed($userNotifRepository, UserInterface $user)
    {
        $userNotifRepository->markAsViewed($user, 'all')->shouldBeCalled();

        $this->markAsViewed($user, 'all');
    }

    function it_can_remove_a_notification(
        $userNotifRepository,
        UserNotification $userNotification,
        UserInterface $user,
        $userNotifRemover
    ) {
        $userNotifRepository->findOneBy(['id' => 1, 'user' => $user])->willReturn($userNotification);
        $userNotifRemover->remove($userNotification)->shouldBeCalled();

        $this->remove($user, 1);
    }
}
