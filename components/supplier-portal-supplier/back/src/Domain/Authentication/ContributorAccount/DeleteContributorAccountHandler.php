<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\ContributorAccountDeleted;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteContributorAccountHandler
{
    public function __construct(
        private ContributorAccountRepository $contributorAccountRepository,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $contributorEmail): void
    {
        $this->contributorAccountRepository->deleteByEmail($contributorEmail);

        $this->logger->info(sprintf('The contributor account "%s" has been deleted', $contributorEmail));

        $this->eventDispatcher->dispatch(new ContributorAccountDeleted($contributorEmail));
    }
}
