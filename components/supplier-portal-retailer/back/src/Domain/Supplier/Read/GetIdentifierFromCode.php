<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetIdentifierFromCode
{
    public function __invoke(string $code): ?string;
}
