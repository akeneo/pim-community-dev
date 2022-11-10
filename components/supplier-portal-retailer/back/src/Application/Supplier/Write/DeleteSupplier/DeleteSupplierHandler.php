<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\DeleteSupplier;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DeleteSupplierHandler
{
    public function __construct(
        private Repository $supplierRepository,
        private GetSupplierWithContributors $getSupplierWithContributors,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(DeleteSupplier $deleteSupplier): void
    {
        $supplierIdentifier = Identifier::fromString($deleteSupplier->identifier);

        $supplierWithContributors = ($this->getSupplierWithContributors)($deleteSupplier->identifier);

        $this->supplierRepository->delete($supplierIdentifier);

        $this->logger->info(
            sprintf('Supplier "%s" deleted.', $deleteSupplier->identifier),
        );

        foreach ($supplierWithContributors->contributors as $contributorEmail) {
            $this->eventDispatcher->dispatch(new ContributorDeleted($supplierIdentifier, $contributorEmail));
        }
    }
}
