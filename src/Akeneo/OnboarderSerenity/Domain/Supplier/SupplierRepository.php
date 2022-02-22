<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

interface SupplierRepository
{
    public function add(Supplier $supplier): void;
    public function find(Identifier $identifier): ?Supplier;
}
