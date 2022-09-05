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
        $notification = $this->createNotification($jobInstance, $errorMessage);
        $usernames = array_map(static fn (UserToNotify $user) => $user->getUsername(), $usersToNotify);

        $this->pimNotifier->notify($notification, $usernames);
    }

    private function createNotification(ScheduledJobInstance $jobInstance, string $errorMessage): NotificationInterface
    {
        $notification = new Notification();

        $notification
            ->setType('error')
            ->setMessage('akeneo.job_automation.notification.invalid_job_instance')
            ->setMessageParams([
                '{{ label }}' => $jobInstance->code,
                '{{ error }}' => $errorMessage
            ])
            ->setRoute(sprintf('pim_importexport_%s_profile_show', $jobInstance->type))
            ->setRouteParams(['code' => $jobInstance->code])
            ->setContext(['actionType' => $jobInstance->type]);

        return $notification;
    }
}
