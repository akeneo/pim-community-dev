<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;

interface SupplierFileRepository
{
    public const RETENTION_DURATION_IN_DAYS = 90;

    public function save(SupplierFile $supplierFile): void;
    public function deleteOld(): void;
}
