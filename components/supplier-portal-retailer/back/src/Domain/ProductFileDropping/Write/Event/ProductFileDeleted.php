<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event;

final class ProductFileDeleted
{
    public function __construct(public string $productFileIdentifier)
    {
    }
}
