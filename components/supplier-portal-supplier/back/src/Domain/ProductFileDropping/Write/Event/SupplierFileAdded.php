<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;

final class SupplierFileAdded
{
    public function __construct(public SupplierFile $supplierFile)
    {
    }
}
