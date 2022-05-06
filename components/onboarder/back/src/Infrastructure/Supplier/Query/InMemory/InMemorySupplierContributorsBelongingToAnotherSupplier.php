<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemorySupplierContributorsBelongingToAnotherSupplier implements SupplierContributorsBelongingToAnotherSupplier
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(string $supplierIdentifier, array $emails): array
    {
        $otherSuppliers = array_filter($this->supplierRepository->findAll(), fn (Supplier $supplier) => $supplier->identifier() !== $supplierIdentifier);

        $contributorEmailsBelongingToAnotherSupplier = [];

        /** @var Supplier $supplier */
        foreach ($otherSuppliers as $supplier) {
            $contributorEmailsBelongingToAnotherSupplier = array_merge(
                $contributorEmailsBelongingToAnotherSupplier,
                array_intersect($emails, array_map(fn (array $contributor) => $contributor['email'], $supplier->contributors())),
            );
        }

        return $contributorEmailsBelongingToAnotherSupplier;
    }
}
