<?php

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\CreateContributorAccountHandler;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
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
