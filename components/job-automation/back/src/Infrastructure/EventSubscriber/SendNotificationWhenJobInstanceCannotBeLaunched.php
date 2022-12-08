<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendNotificationWhenJobInstanceCannotBeLaunched implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotifierInterface $pimNotifier,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CouldNotLaunchAutomatedJobEvent::class => 'notifyUsers',
        ];
    }

    public function notifyUsers(CouldNotLaunchAutomatedJobEvent $event): void
    {
        match ($event->reason) {
            CouldNotLaunchAutomatedJobEvent::INVALID_JOB_REASON => $this->notifyUsersInvalidJobInstance($event),
            CouldNotLaunchAutomatedJobEvent::INTERNAL_ERROR_REASON => $this->notifyUsersInternalError($event),
            default => throw new \RuntimeException(sprintf('Unable to notify users for this reason : %s', $event->reason)),
        };
    }

    public function notifyUsersInvalidJobInstance(CouldNotLaunchAutomatedJobEvent $event): void
    {
        $notification = $this->createNotification(
            'akeneo.job_automation.notification.invalid_job_instance',
            [
                '{{ type }}' => $event->scheduledJobInstance->type,
                '{{ label }}' => $event->scheduledJobInstance->code,
                '{{ error }}' => implode(' ', $event->errorMessages),
            ],
            $event->scheduledJobInstance->code,
            $event->scheduledJobInstance->type,
        );

        $this->pimNotifier->notify($notification, $event->userToNotify->getUsernames());
    }

    public function notifyUsersInternalError(CouldNotLaunchAutomatedJobEvent $event): void
    {
        $notification = $this->createNotification(
            'akeneo.job_automation.notification.internal_error',
            [],
            $event->scheduledJobInstance->code,
            $event->scheduledJobInstance->type,
        );

        $this->pimNotifier->notify($notification, $event->userToNotify->getUsernames());
    }

    /**
     * @param array<string, string> $messageParams
     */
    private function createNotification(string $message, array $messageParams, string $jobInstanceCode, string $jobInstanceType): NotificationInterface
    {
        $notification = new Notification();

        $notification
            ->setType('error')
            ->setMessage($message)
            ->setMessageParams($messageParams)
            ->setRoute(sprintf('pim_importexport_%s_profile_show', $jobInstanceType))
            ->setRouteParams(['code' => $jobInstanceCode])
            ->setContext(['actionType' => $jobInstanceType]);

        return $notification;
    }
}
