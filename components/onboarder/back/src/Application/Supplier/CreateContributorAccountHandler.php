<?php

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event\ContributorAccountCreated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateContributorAccountHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    public function __invoke(CreateContributorAccount $command): void
    {
        $contributorAccount = ContributorAccount::fromEmail($command->contributorEmail);

        $this->contributorAccountRepository->save($contributorAccount);

        $this->eventDispatcher->dispatch(new ContributorAccountCreated($contributorAccount));
    }
}
