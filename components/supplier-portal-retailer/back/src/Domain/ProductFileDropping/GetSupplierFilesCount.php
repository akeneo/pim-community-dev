<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface GetSupplierFilesCount
{
    public function __invoke(string $supplierIdentifier): int;
}
