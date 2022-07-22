<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFile;

interface GetProductFiles
{
    /**
     * @return SupplierFile[]
     */
    public function __invoke(Identifier $supplierIdentifier): array;
}
