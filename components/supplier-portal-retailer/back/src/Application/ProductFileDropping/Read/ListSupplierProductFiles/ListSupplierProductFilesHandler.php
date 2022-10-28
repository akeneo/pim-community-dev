<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles\ListSupplierProductFiles as ListSupplierProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles;

final class ListSupplierProductFilesHandler
{
    public function __construct(
        private ListSupplierProductFiles $listSupplierProductFiles,
        private CountProductFiles $countProductFiles,
    ) {
    }

    public function __invoke(ListSupplierProductFilesQuery $listSupplierProductFiles): ProductFiles
    {
        return new ProductFiles(
            ($this->listSupplierProductFiles)(
                $listSupplierProductFiles->supplierIdentifier,
                $listSupplierProductFiles->page
            ),
            ($this->countProductFiles)(),
        );
    }
}
