<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateContributorAccountOnContributorAdded implements EventSubscriberInterface
{
    public function __construct(
        private CreateContributorAccountHandler $createContributorAccountHandler,
        private FeatureFlag $contributorAuthenticationFeatureFlag,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorAdded::class => 'contributorAdded',
        ];
    }

    public function contributorAdded(ContributorAdded $contributorAdded): void
    {
        if (!$this->contributorAuthenticationFeatureFlag->isEnabled()) {
            return;
        }

        ($this->createContributorAccountHandler)(new CreateContributorAccount($contributorAdded->contributorEmail()));
    }
}
