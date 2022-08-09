<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read;

interface GetSupplierFilePath
{
    public function __invoke(string $supplierFileIdentifier): ?string;
}
