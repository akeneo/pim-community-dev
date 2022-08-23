<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;

interface SupplierFileRepository
{
    public const NUMBER_OF_DAYS_AFTER_WHICH_THE_FILES_ARE_CONSIDERED_OLD = 90;

    public function save(SupplierFile $supplierFile): void;
    public function deleteOld(): void;
}
