<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\DeleteContributorAccountHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteContributorAccountOnContributorDeleted implements EventSubscriberInterface
{
    public function __construct(private DeleteContributorAccountHandler $deleteContributorAccountHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorDeleted::class => 'contributorDeleted',
        ];
    }

    public function contributorDeleted(ContributorDeleted $contributorDeleted): void
    {
        ($this->deleteContributorAccountHandler)($contributorDeleted->contributorEmail());
    }
}
