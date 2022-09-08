<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;

final class ProductFileAdded
{
    public function __construct(private ProductFile $productFile, private string $supplierLabel)
    {
    }

    public function supplierLabel(): string
    {
        return $this->supplierLabel;
    }

    public function contributorEmail(): string
    {
        return $this->productFile->contributorEmail();
    }
}
