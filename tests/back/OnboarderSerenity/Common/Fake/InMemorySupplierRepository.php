<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Common\Fake;

use Akeneo\OnboarderSerenity\Domain\Supplier\Identifier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\SupplierRepository;
use JetBrains\PhpStorm\Pure;

final class InMemorySupplierRepository implements SupplierRepository
{
    private array $suppliers = [];

    public function save(Supplier $supplier): void
    {
        $this->suppliers[$supplier->identifier()] = $supplier;
    }

    #[Pure]
    public function find(Identifier $identifier): ?Supplier
    {
        foreach ($this->suppliers as $identifier => $supplier) {
            if ((string) $identifier === $supplier->identifier()) {
                return $supplier;
            }
        }

        return null;
    }
}
