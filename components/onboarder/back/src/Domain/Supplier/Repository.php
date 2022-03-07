<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

interface Repository
{
    public function save(Supplier $supplier): void;
    public function find(Identifier $identifier): ?Supplier;
}
