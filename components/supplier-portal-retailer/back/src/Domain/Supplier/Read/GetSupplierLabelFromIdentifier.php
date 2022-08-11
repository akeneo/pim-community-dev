<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetSupplierLabelFromIdentifier
{
    public function __invoke(string $identifier): ?string;
}
