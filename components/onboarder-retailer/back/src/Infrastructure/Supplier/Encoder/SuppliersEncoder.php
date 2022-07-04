<?php

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Encoder;

interface SuppliersEncoder
{
    public function __invoke(array $suppliersWithContributors): string;
}
