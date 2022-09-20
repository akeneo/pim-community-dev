<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFile;

final class GetProductFileQuery
{
    public function __construct(public string $productFileIdentifier)
    {
    }
}
