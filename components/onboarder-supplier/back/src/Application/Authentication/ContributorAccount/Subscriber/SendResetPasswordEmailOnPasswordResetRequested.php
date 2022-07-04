<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event\ResetPasswordRequested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendResetPasswordEmailOnPasswordResetRequested implements EventSubscriberInterface
{
    public function __construct(private SendResetPasswordEmailHandler $sendResetPasswordEmailHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResetPasswordRequested::class => 'sendResetPasswordEmail',
        ];
    }

    public function sendResetPasswordEmail(ResetPasswordRequested $event): void
    {
        ($this->sendResetPasswordEmailHandler)(
            new SendResetPasswordEmail(
                $event->contributorAccountEmail,
                $event->accessToken,
            )
        );
    }
}
