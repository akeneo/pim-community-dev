<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface ListProductFilesForSupplier
{
    public const NUMBER_OF_SUPPLIER_FILES_PER_PAGE = 25;

    public function __invoke(string $supplierIdentifier, int $page = 1): array;
}
