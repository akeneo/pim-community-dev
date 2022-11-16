<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;

interface ProductFileImportRepository
{
    public function save(ProductFileImport $productFileImport): void;

    public function findByJobExecutionId(int $jobExecutionId): ?ProductFileImport;
}
