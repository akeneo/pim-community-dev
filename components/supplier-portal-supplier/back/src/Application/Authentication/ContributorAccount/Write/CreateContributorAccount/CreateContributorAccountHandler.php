<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountCreated;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateContributorAccountHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateContributorAccount $command): void
    {
        $contributorAccount = ContributorAccount::fromEmail($command->contributorEmail);

        $this->contributorAccountRepository->save($contributorAccount);

        $this->eventDispatcher->dispatch(new ContributorAccountCreated($contributorAccount));
    }
}
