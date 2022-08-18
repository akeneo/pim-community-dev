<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\DeleteContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\DeleteContributorAccountHandler;
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
        ($this->deleteContributorAccountHandler)(new DeleteContributorAccount($contributorDeleted->contributorEmail()));
    }
}
