<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Domain\Read;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Contributor\Repository\InMemory\InMemoryRepository as ContributorRepository;

final class InMemoryGetSupplier implements GetSupplier
{
    public function __construct(private InMemoryRepository $supplierRepository, private ContributorRepository $contributoryRepository)
    {
    }

    public function __invoke(Write\Supplier\ValueObject\Identifier $identifier): ?Read\Supplier\Model\Supplier
    {
        $supplier = $this->supplierRepository->find($identifier);

        if ($supplier === null) {
            return null;
        }

        $contributors = $this->contributoryRepository->findBySupplier(Write\Supplier\ValueObject\Identifier::fromString($supplier->identifier()));

        $contributors = array_map(
            fn (Write\Supplier\Contributor\Model\Contributor $contributor) => new Read\Supplier\Model\Contributor($contributor->identifier(), $contributor->email()),
            $contributors
        );

        return new Read\Supplier\Model\Supplier(
            $supplier->identifier(),
            $supplier->code(),
            $supplier->label(),
            $contributors
        );
    }
}
