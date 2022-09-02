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

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

final class SendEmailWhenJobInstanceCannotBeLaunched implements EventSubscriberInterface
{
    public function __construct(
        private MailNotifierInterface $mailNotifier,
        private Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CouldNotLaunchAutomatedJobEvent::class => 'notifyUsersInvalidJobInstance',
        ];
    }

    public function notifyUsersInvalidJobInstance(CouldNotLaunchAutomatedJobEvent $event): void
    {
        $emails = $event->userToNotify->getUniqueEmails();
        $parameters = [
            'jobInstance' => $event->scheduledJobInstance,
            'errors' => $event->errorMessages,
        ];

        $txtBody = $this->twig->render('@AkeneoJobAutomation/Mail/invalid_job_instance.txt.twig', $parameters);
        $htmlBody = $this->twig->render('@AkeneoJobAutomation/Mail/invalid_job_instance.html.twig', $parameters);

        foreach ($emails as $email) {
            $this->mailNotifier->notifyByEmail(
                $email,
                'Could not launch scheduled job instance',
                $txtBody,
                $htmlBody,
            );
        }
    }
}
