<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;

final class SupplierFileAdded
{
    public function __construct(private SupplierFile $supplierFile)
    {
    }

    public function supplierLabel(): string
    {
        return $this->supplierFile->supplierLabel();
    }

    public function contributorEmail(): string
    {
        return $this->supplierFile->contributorEmail();
    }
}
