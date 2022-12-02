<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

interface GetSupplierProductFilesCount
{
    public function __invoke(
        string $supplierIdentifier,
        string $search = '',
        ?ProductFileImportStatus $status = null
    ): int;
}
