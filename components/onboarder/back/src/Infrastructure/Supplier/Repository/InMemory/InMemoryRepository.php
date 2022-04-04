<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;

class InMemoryRepository implements Supplier\Repository
{
    private array $suppliers = [];

    public int $saveCallCounter = 0;

    public function save(Supplier\Model\Supplier $supplier): void
    {
        $this->suppliers[$supplier->identifier()] = $supplier;
        $this->saveCallCounter++;
    }

    public function find(Supplier\ValueObject\Identifier $identifier): ?Supplier\Model\Supplier
    {
        return \array_key_exists((string) $identifier, $this->suppliers) ? $this->suppliers[(string) $identifier] : null;
    }

    public function delete(Identifier $identifier): void
    {
        if (array_key_exists((string) $identifier, $this->suppliers)) {
            unset($this->suppliers[(string) $identifier]);
        }
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
        return \count($this->suppliers);
    }

    public function findAll(): array
    {
        return $this->suppliers;
    }
}
