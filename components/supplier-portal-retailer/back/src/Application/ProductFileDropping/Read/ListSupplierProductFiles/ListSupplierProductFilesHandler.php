<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles\ListSupplierProductFiles as ListSupplierProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

final class ListSupplierProductFilesHandler
{
    public function __construct(
        private readonly ListSupplierProductFiles $listSupplierProductFiles,
        private readonly GetSupplierProductFilesCount $getSupplierProductFilesCount,
    ) {
    }

    public function __invoke(ListSupplierProductFilesQuery $listSupplierProductFiles): ProductFiles
    {
        $status = null === $listSupplierProductFiles->status
            ? null
            : ProductFileImportStatus::from($listSupplierProductFiles->status);

        return new ProductFiles(
            ($this->listSupplierProductFiles)(
                $listSupplierProductFiles->supplierIdentifier,
                $listSupplierProductFiles->page,
                $listSupplierProductFiles->search,
                $status,
            ),
            ($this->getSupplierProductFilesCount)(
                $listSupplierProductFiles->supplierIdentifier,
                $listSupplierProductFiles->search,
                $status,
            ),
        );
    }
}
