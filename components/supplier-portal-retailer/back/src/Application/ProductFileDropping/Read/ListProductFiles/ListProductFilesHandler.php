<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles\ListProductFiles as ListProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountAllProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountFilteredProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

final class ListProductFilesHandler
{
    public function __construct(
        private readonly ListProductFiles $listProductFiles,
        private readonly CountAllProductFiles $countProductFiles,
        private readonly CountFilteredProductFiles $countFilteredProductFiles,
    ) {
    }

    public function __invoke(ListProductFilesQuery $listProductFiles): ProductFiles
    {
        $status = null === $listProductFiles->status
            ? null
            : ProductFileImportStatus::from($listProductFiles->status);

        return new ProductFiles(
            ($this->listProductFiles)($listProductFiles->page, $listProductFiles->search, $status),
            ($this->countProductFiles)(),
            ($this->countFilteredProductFiles)($listProductFiles->search, $status),
        );
    }
}
