<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

interface StoreProductsFile
{
    public function __invoke(
        Code $supplierCode,
        Filename $originalFilename,
        Identifier $identifier,
        string $temporaryPath,
    ): string;
}
