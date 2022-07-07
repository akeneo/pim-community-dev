<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendResetPasswordEmailOnPasswordReset implements EventSubscriberInterface
{
    public function __construct(private SendResetPasswordEmailHandler $sendResetPasswordEmailHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PasswordReset::class => 'sendResetPasswordEmail',
        ];
    }

    public function sendResetPasswordEmail(PasswordReset $event): void
    {
        ($this->sendResetPasswordEmailHandler)(
            new SendResetPasswordEmail(
                $event->contributorAccountEmail,
                $event->accessToken,
            )
        );
    }
}
