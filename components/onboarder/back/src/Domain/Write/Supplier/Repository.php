<?php

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier;

interface Repository
{
    public function save(Supplier $supplier): void;
    public function find(Identifier $identifier): ?Supplier;
}
