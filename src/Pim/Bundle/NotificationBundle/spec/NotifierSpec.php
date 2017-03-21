<?php

namespace spec\Pim\Bundle\NotificationBundle;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class NotifierSpec extends ObjectBehavior
{
    function let(
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver
    ) {
        $this->beConstructedWith($userNotifFactory, $userProvider, $notificationSaver, $userNotifsSaver);
    }

    function it_can_create_a_notification_from_username(
        $notificationSaver,
        $userNotifsSaver,
        $userNotifFactory,
        $userProvider,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        UserInterface $user
    ) {
        $userProvider->loadUserByUsername('author')->willReturn($user);
        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->notify($notification, ['author']);
    }

    function it_can_create_a_notification_from_user(
        $notificationSaver,
        $userNotifsSaver,
        $userNotifFactory,
        $userProvider,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        UserInterface $user
    ) {
        $userProvider->loadUserByUsername()->shouldNotBeCalled();
        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->notify($notification, [$user]);
    }

    function it_can_create_multiple_notifications(
        $notificationSaver,
        $userNotifsSaver,
        $userNotifFactory,
        $userProvider,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        UserNotificationInterface $userNotificationAuthor,
        UserInterface $user,
        UserInterface $userAuthor
    ) {
        $userProvider->loadUserByUsername('author')->willReturn($userAuthor);
        $userNotifFactory->createUserNotification($notification, $userAuthor)->willReturn($userNotificationAuthor);

        $userProvider->loadUserByUsername($user)->shouldNotBeCalled();
        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification, $userNotificationAuthor])->shouldBeCalled();

        $this->notify($notification, [$user, 'author']);
    }
}
