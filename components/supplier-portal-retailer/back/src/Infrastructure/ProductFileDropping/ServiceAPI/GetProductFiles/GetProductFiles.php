<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier\ListProductFilesForSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile as ProductFileReadModel;

final class GetProductFiles
{
    public function __construct(
        private ListProductFilesForSupplierHandler $listProductFilesForSupplierHandler,
    ) {
    }

    public function __invoke(GetProductFilesQuery $getProductFilesQuery): ProductFiles
    {
        $productFiles = ($this->listProductFilesForSupplierHandler)(
            new ListProductFilesForSupplier(
                $getProductFilesQuery->contributorEmail,
                $getProductFilesQuery->page,
                $getProductFilesQuery->search,
            )
        );

        return new ProductFiles(array_map(
            fn (ProductFileReadModel $productFileReadModel) => ProductFile::fromReadModel($productFileReadModel),
            $productFiles->productFiles,
        ), $productFiles->totalProductFilesCount, $productFiles->totalSearchResultsCount);
    }
}
