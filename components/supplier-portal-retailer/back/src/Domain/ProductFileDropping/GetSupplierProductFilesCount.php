<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface GetSupplierProductFilesCount
{
    public function __invoke(string $supplierIdentifier, string $search = ''): int;
}
