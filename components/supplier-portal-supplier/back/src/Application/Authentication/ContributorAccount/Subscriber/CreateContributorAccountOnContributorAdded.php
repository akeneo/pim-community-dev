<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateContributorAccountOnContributorAdded implements EventSubscriberInterface
{
    public function __construct(
        private CreateContributorAccountHandler $createContributorAccountHandler,
        private FeatureFlags $featureFlags,
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
        if (!$this->featureFlags->isEnabled('supplier_portal_contributor_authentication')) {
            return;
        }

        ($this->createContributorAccountHandler)(new CreateContributorAccount($contributorAdded->contributorEmail()));
    }
}
