<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendResetPasswordEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendResetPasswordEmailOnPasswordReset implements EventSubscriberInterface
{
    public function __construct(private SendResetPasswordEmail $sendResetPasswordEmail, private LoggerInterface $logger)
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

        $this->logger->info(sprintf('A reset password email has been sent to "%s"', $event->contributorAccountEmail));
    }
}
