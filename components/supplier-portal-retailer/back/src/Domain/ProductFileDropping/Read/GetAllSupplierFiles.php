<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read;

interface GetAllSupplierFiles
{
    public const NUMBER_OF_SUPPLIER_FILES_PER_PAGE = 25;

    public function __invoke(int $page = 1): array;
}
