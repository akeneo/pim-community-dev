<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier\ListProductFilesForSupplier as ListProductFilesForSupplierQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;

class ListProductFilesForSupplierHandler
{
    public function __construct(
        private ListProductFilesForSupplier $listProductFilesForSupplier,
        private GetSupplierFromContributorEmail $getSupplierFromContributorEmail,
        private GetSupplierProductFilesCount $getProductFilesCount,
    ) {
    }

    public function __invoke(ListProductFilesForSupplierQuery $listProductFiles): ProductFiles
    {
        $supplier = ($this->getSupplierFromContributorEmail)($listProductFiles->contributorEmail);

        if (null === $supplier) {
            return new ProductFiles([], 0);
        }

        return new ProductFiles(
            ($this->listProductFilesForSupplier)($supplier->identifier, $listProductFiles->page),
            ($this->getProductFilesCount)($supplier->identifier, $listProductFiles->search),
        );
    }
}
