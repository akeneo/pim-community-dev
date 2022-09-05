<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface GetProductFilesCount
{
    public function __invoke(string $supplierIdentifier): int;
}
