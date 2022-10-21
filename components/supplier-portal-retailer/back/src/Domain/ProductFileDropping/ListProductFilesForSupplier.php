<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;

interface ListProductFilesForSupplier
{
    public const NUMBER_OF_PRODUCT_FILES_PER_PAGE = 10;

    /**
     * @return ProductFile[]
     */
    public function __invoke(string $supplierIdentifier, int $page = 1): array;
}
