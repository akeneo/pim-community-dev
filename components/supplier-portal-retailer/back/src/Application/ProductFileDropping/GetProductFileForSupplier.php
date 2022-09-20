<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

final class GetProductFileForSupplier
{
    public function __construct(public string $productFileIdentifier)
    {
    }
}
