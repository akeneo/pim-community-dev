<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemorySupplierExists implements SupplierExists
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function fromCode(Code $supplierCode): bool
    {
        return null !== $this->supplierRepository->findByCode($supplierCode);
    }
}
