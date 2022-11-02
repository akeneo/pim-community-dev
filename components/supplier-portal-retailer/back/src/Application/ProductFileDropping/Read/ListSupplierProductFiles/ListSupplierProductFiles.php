<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles;

final class ListSupplierProductFiles
{
    public function __construct(public string $supplierIdentifier, public int $page)
    {
    }
}
