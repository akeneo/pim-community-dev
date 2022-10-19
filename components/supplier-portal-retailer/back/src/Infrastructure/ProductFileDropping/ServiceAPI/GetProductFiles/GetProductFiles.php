<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile as ProductFileReadModel;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;

final class GetProductFiles
{
    public function __construct(
        private ListProductFilesForSupplier $listProductFilesForSupplier,
        private GetSupplierFromContributorEmail $getSupplierFromContributorEmail,
        private GetProductFilesCount $getProductFilesCount,
    ) {
    }

    public function __invoke(GetProductFilesQuery $getProductFilesQuery): array
    {
        $supplier = ($this->getSupplierFromContributorEmail)($getProductFilesQuery->contributorEmail);

        if (null === $supplier) {
            return [];
        }

        return [
            'product_files' => array_map(
                fn (ProductFileReadModel $productFileReadModel) => ProductFile::fromReadModel($productFileReadModel),
                ($this->listProductFilesForSupplier)($supplier->identifier, $getProductFilesQuery->page),
            ),
            'total' => ($this->getProductFilesCount)($supplier->identifier),
        ];
    }
}
