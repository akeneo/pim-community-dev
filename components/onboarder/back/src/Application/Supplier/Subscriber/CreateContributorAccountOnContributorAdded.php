<?php

namespace Akeneo\OnboarderSerenity\Application\Supplier\Subscriber;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateContributorAccount;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateContributorAccountHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAdded;
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
