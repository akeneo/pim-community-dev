<?php

namespace Specification\Akeneo\Platform\Bundle\NotificationBundle;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\UserNotificationFactory;
use Prophecy\Argument;
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
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        UserInterface $user
    ) {
        $userProvider->loadUserByIdentifier('author')->willReturn($user);
        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->notify($notification, ['author']);
    }

    function it_can_create_a_notification_from_user(
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        UserInterface $user
    ) {
        $userProvider->loadUserByIdentifier()->shouldNotBeCalled();
        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification])->shouldBeCalled();

        $this->notify($notification, [$user]);
    }

    function it_can_create_multiple_notifications(
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        UserNotificationFactory $userNotifFactory,
        UserProviderInterface $userProvider,
        NotificationInterface $notification,
        UserNotificationInterface $userNotification,
        UserNotificationInterface $userNotificationAuthor,
        UserInterface $user,
        UserInterface $userAuthor
    ) {
        $userProvider->loadUserByIdentifier('author')->willReturn($userAuthor);
        $userNotifFactory->createUserNotification($notification, $userAuthor)->willReturn($userNotificationAuthor);

        $userProvider->loadUserByIdentifier($user)->shouldNotBeCalled();
        $userNotifFactory->createUserNotification($notification, $user)->willReturn($userNotification);

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([$userNotification, $userNotificationAuthor])->shouldBeCalled();

        $this->notify($notification, [$user, 'author']);
    }

    function it_does_not_notify_the_system_user(
        SaverInterface $notificationSaver,
        BulkSaverInterface $userNotifsSaver,
        UserNotificationFactory $userNotifFactory,
        NotificationInterface $notification,
        UserInterface $userSystem
    ) {
        $userSystem->getUserIdentifier()->willReturn('system');

        $userNotifFactory->createUserNotification(Argument::cetera())->shouldNotBeCalled();

        $notificationSaver->save($notification)->shouldBeCalled();
        $userNotifsSaver->saveAll([])->shouldBeCalled();

        $this->notify($notification, [$userSystem, 'system']);
    }
}
