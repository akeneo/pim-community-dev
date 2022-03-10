<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test_fake')]
class InMemoryRepository implements Supplier\Repository
{
    private array $suppliers = [];

    public function save(Supplier\Model\Supplier $supplier): void
    {
        $this->suppliers[$supplier->identifier()] = $supplier;
    }

    public function find(Supplier\ValueObject\Identifier $identifier): ?Supplier\Model\Supplier
    {
        if (array_key_exists((string) $identifier, $this->suppliers)) {
            return $this->suppliers[(string) $identifier];
        }

        return null;
    }

    public function findByCode(Supplier\ValueObject\Code $code): ?Supplier\Model\Supplier
    {
        foreach ($this->suppliers as $supplier) {
            if ((string) $code === $supplier->code()) {
                return $supplier;
            }
        }

        return null;
    }

    public function count(): int
    {
        return count($this->suppliers);
    }

    public function findAll(): array
    {
        return $this->suppliers;
    }
}
