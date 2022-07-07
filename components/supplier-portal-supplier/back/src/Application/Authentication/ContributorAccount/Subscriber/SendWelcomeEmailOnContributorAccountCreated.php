<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendWelcomeEmailOnContributorAccountCreated implements EventSubscriberInterface
{
    public function __construct(private SendWelcomeEmailHandler $sendWelcomeEmailHandler)
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
        ($this->sendWelcomeEmailHandler)($event->contributorAccount->email(), $event->contributorAccount->accessToken());
    }
}
