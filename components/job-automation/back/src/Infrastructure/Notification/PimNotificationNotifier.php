<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\UserNotifierInterface;

class PimNotificationNotifier implements UserNotifierInterface
{
    public function __construct(
        private NotifierInterface $pimNotifier
    ) {
    }

    public function forInvalidJobInstance(
        array $usersToNotify,
        ScheduledJobInstance $jobInstance,
        string $errorMessage,
    ): void {
        $notification = $this->createNotification();
        $usernames = array_map(static fn (UserToNotify $user) => $user->getUsername(), $usersToNotify);

        $this->pimNotifier->notify($notification, $usernames);
    }

    private function createNotification(): NotificationInterface
    {
        return new Notification();
    }
}
