<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount\CreateContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount\CreateContributorAccountHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateContributorAccountOnContributorAdded implements EventSubscriberInterface
{
    public function __construct(private CreateContributorAccountHandler $createContributorAccountHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorAdded::class => 'contributorAdded',
        ];
    }

    public function contributorAdded(ContributorAdded $contributorAdded): void
    {
        ($this->createContributorAccountHandler)(new CreateContributorAccount($contributorAdded->contributorEmail()));
    }
}
