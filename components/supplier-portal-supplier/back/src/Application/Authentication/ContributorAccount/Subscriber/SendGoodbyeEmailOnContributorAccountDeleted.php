<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendGoodbyeEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendGoodbyeEmailOnContributorAccountDeleted implements EventSubscriberInterface
{
    public function __construct(private SendGoodbyeEmail $sendGoodbyeEmail)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorAccountDeleted::class => 'sendGoodbyeEmail',
        ];
    }

    public function sendGoodbyeEmail(ContributorAccountDeleted $event): void
    {
        ($this->sendGoodbyeEmail)($event->contributorEmail);
    }
}
