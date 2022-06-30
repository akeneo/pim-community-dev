<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Event\ResetPasswordRequested;

final class SendResetPasswordEmailOnPasswordResetRequested
{
    public function __construct(private SendResetPasswordEmailHandler $sendResetPasswordEmailHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResetPasswordRequested::class => 'sendResetPassword',
        ];
    }

    public function sendResetPassword(ResetPasswordRequested $event): void
    {
        ($this->sendResetPasswordEmailHandler)(
            new SendResetPasswordEmail(
                $event->contributorAccountEmail,
                $event->accessToken,
            )
        );
    }
}
