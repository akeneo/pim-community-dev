<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;

interface GetProductFilePathAndFileNameForSupplier
{
    public function __invoke(string $productFileIdentifier, string $supplierIdentifier): ?ProductFilePathAndFileName;
}
