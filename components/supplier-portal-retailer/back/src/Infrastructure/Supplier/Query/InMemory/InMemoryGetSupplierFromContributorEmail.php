<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier as SupplierReadModel;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetSupplierFromContributorEmail implements GetSupplierFromContributorEmail
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(string $contributorEmail): ?SupplierReadModel
    {
        /** @var Supplier $supplier */
        foreach ($this->supplierRepository->findAll() as $supplier) {
            foreach ($supplier->contributors() as $contributor) {
                if ($contributorEmail === $contributor['email']) {
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
