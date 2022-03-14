<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemorySupplierExists implements SupplierExists
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function fromCode(Supplier\ValueObject\Code $supplierCode): bool
    {
        return null !== $this->supplierRepository->findByCode($supplierCode);
    }
}
