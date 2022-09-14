<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SendNotificationWhenJobInstanceCannotBeLaunched implements EventSubscriberInterface
{
    public function __construct(
        private NotifierInterface $pimNotifier,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            CouldNotLaunchAutomatedJobEvent::class => 'notifyUsersInvalidJobInstance',
        ];
    }

    public function notifyUsersInvalidJobInstance(CouldNotLaunchAutomatedJobEvent $event): void
    {
        $notification = $this->createNotification($event->scheduledJobInstance, $event->errorMessages);
        $this->pimNotifier->notify($notification, $event->userToNotify->getUsernames());
    }

    private function createNotification(ScheduledJobInstance $jobInstance, array $errorMessage): NotificationInterface
    {
        $notification = new Notification();

        $notification
            ->setType('error')
            ->setMessage('akeneo.job_automation.notification.invalid_job_instance')
            ->setMessageParams([
                '{{ type }}' => $jobInstance->type,
                '{{ label }}' => $jobInstance->code,
                '{{ error }}' => implode(' ', array_map(fn (String $error) => $this->translator->trans($error), $errorMessage)),
            ])
            ->setRoute(sprintf('pim_importexport_%s_profile_show', $jobInstance->type))
            ->setRouteParams(['code' => $jobInstance->code])
            ->setContext(['actionType' => $jobInstance->type]);

        return $notification;
    }
}
