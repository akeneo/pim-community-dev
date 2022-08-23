<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendWelcomeEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendWelcomeEmailOnContributorAccountCreated implements EventSubscriberInterface
{
    public function __construct(private SendWelcomeEmail $sendWelcomeEmail, private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorAccountCreated::class => 'sendWelcomeEmail',
        ];
    }

    public function sendWelcomeEmail(ContributorAccountCreated $event): void
    {
        ($this->sendWelcomeEmail)($event->contributorAccount->email(), $event->contributorAccount->accessToken());

        $this->logger->info(sprintf('A welcome email has been sent to "%s"', $event->contributorAccount->email()));
    }
}
