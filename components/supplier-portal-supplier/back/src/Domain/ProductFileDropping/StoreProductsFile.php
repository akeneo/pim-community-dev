<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;

interface StoreProductsFile
{
    public function __invoke(
        Code $supplierCode,
        Filename $originalFilename,
        Identifier $identifier,
        string $temporaryPath,
    ): string;
}
