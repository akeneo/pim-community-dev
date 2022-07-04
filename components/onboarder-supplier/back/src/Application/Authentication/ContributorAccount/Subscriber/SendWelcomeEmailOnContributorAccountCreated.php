<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event\ContributorAccountCreated;
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
        ($this->sendWelcomeEmailHandler)(
            new SendWelcomeEmail(
                $event->contributorAccount->accessToken(),
                $event->contributorAccount->email(),
            )
        );
    }
}
