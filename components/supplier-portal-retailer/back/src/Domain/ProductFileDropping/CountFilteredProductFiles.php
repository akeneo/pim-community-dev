<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

interface CountFilteredProductFiles
{
    public function __invoke(string $search = '', ?ProductFileImportStatus $status = null): int;
}
