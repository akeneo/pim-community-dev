<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;

interface ProductFileRepository
{
    public const RETENTION_DURATION_IN_DAYS = 90;

    public function save(ProductFile $productFile): void;
    public function find(Identifier $identifier): ?ProductFile;
    public function deleteProductFileRetailerComments(string $productFileIdentifier): void;
    public function deleteProductFileSupplierComments(string $productFileIdentifier): void;
    public function deleteOldProductFiles(): void;
}
