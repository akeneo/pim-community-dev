<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;

class InMemoryRepository implements Repository
{
    private array $suppliers = [];

    public int $saveCallCounter = 0;

    public function save(Supplier $supplier): void
    {
        $this->suppliers[$supplier->identifier()] = $supplier;
        $this->saveCallCounter++;
    }

    public function find(Identifier $identifier): ?Supplier
    {
        return \array_key_exists((string) $identifier, $this->suppliers) ? $this->suppliers[(string) $identifier] : null;
    }

    public function delete(Identifier $identifier): void
    {
        if (array_key_exists((string) $identifier, $this->suppliers)) {
            unset($this->suppliers[(string) $identifier]);
        }
    }

    public function findByCode(Code $code): ?Supplier
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
