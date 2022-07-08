<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendResetPasswordEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendResetPasswordEmailOnPasswordReset implements EventSubscriberInterface
{
    public function __construct(private SendResetPasswordEmail $sendResetPasswordEmail)
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
        ($this->sendResetPasswordEmail)($event->contributorAccountEmail, $event->accessToken);
    }
}
