<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

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
