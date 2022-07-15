<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;

interface StoreProductsFile
{
    public function __invoke(string $supplierCode, Filename $filename, string $content): void;
}
