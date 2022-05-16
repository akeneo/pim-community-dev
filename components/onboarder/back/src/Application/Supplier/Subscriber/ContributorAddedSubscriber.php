<?php

namespace Akeneo\OnboarderSerenity\Application\Supplier\Subscriber;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateContributorAccountHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAdded;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContributorAddedSubscriber implements EventSubscriberInterface
{
    public function __construct(private CreateContributorAccountHandler $createContributorAccountHandler)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorAdded::class => 'onContributorAdded',
        ];
    }

    public function onContributorAdded(ContributorAdded $contributorAdded): void
    {
        ($this->createContributorAccountHandler)(new CreateContributorAccount($contributorAdded->contributorEmail()));
    }
}
