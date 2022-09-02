<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\SupplierFile;

interface SupplierFileRepository
{
    public function save(SupplierFile $supplierFile): void;
}
