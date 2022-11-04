<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

interface ProductFileRepository
{
    public function save(ProductFile $productFile): void;
    public function find(Identifier $identifier): ?ProductFile;
    public function deleteProductFileRetailerComments(string $productFileIdentifier): void;
    public function deleteProductFileSupplierComments(string $productFileIdentifier): void;
}
