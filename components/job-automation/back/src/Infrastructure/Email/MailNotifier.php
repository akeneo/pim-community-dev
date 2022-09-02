<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Email;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\UserNotifierInterface;

class MailNotifier implements UserNotifierInterface
{
    public function __construct(
        private MailNotifierInterface $mailNotifier,
    ) {
    }

    public function forInvalidJobInstance(
        UserToNotifyCollection $usersToNotify,
        ScheduledJobInstance $jobInstance,
        string $errorMessage,
    ): void {
        $emails = $usersToNotify->getUniqueEmails();

        // TODO: generate real html/txt bodies through twig once we'll have wording & template
        $subject = $errorMessage;
        $htmlBody = $errorMessage;
        $txtBody = $errorMessage;

        foreach ($emails as $email) {
            $this->mailNotifier->notifyByEmail(
                $email,
                $subject,
                $txtBody,
                $htmlBody,
            );
        }
    }
}
