<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

interface SupplierRepository
{
    public function save(Supplier $supplier): void;
    public function find(Identifier $identifier): ?Supplier;
}
