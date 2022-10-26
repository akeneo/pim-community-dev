<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles\ListProductFiles as ListProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;

final class ListProductFilesHandler
{
    public function __construct(
        private ListProductFiles $listProductFiles,
        private CountProductFiles $countProductFiles,
    ) {
    }

    public function __invoke(ListProductFilesQuery $listProductFiles): ProductFiles
    {
        return new ProductFiles(
            ($this->listProductFiles)($listProductFiles->page),
            ($this->countProductFiles)(),
        );
    }
}
