<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithHasUnreadComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

interface ListProductFiles
{
    public const NUMBER_OF_PRODUCT_FILES_PER_PAGE = 25;

    /**
     * @return ProductFileWithHasUnreadComments[]
     */
    public function __invoke(int $page = 1, string $search = '', ?ProductFileImportStatus $status = null): array;
}
