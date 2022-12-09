<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles;

final class ListSupplierProductFiles
{
    public function __construct(
        public readonly string $supplierIdentifier,
        public readonly int $page,
        public readonly string $search,
        public readonly ?string $status,
    ) {
    }
}
