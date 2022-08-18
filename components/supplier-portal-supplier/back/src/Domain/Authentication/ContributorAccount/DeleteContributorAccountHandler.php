<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteContributorAccountHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(string $contributorEmail): void
    {
        $this->contributorAccountRepository->deleteByEmail($contributorEmail);

        $this->eventDispatcher->dispatch(new ContributorAccountDeleted($contributorEmail));
    }
}
