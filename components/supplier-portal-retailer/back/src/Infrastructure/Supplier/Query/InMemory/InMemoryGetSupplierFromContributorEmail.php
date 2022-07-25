<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier as SupplierReadModel;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;

final class InMemoryGetSupplierFromContributorEmail implements GetSupplierFromContributorEmail
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(ContributorEmail $contributorEmail): ?SupplierReadModel
    {
        /** @var Supplier $supplier */
        foreach ($this->supplierRepository->findAll() as $supplier) {
            foreach ($supplier->contributors() as $contributor) {
                if ((string) $contributorEmail === $contributor['email']) {
                    return new SupplierReadModel(
                        $supplier->identifier(),
                        $supplier->code(),
                        $supplier->label(),
                    );
                }
            }
        }

        return null;
    }
}
