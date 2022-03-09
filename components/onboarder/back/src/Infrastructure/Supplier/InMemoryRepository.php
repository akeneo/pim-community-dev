<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class InMemoryRepository implements Supplier\Repository
{
    private array $suppliers = [];

    public function save(Supplier\Model\Supplier $supplier): void
    {
        $this->suppliers[$supplier->identifier()] = $supplier;
    }

    public function find(Supplier\ValueObject\Identifier $identifier): ?Supplier\Model\Supplier
    {
        foreach ($this->suppliers as $identifier => $supplier) {
            if ((string) $identifier === $supplier->identifier()) {
                return $supplier;
            }
        }

        return null;
    }
}
