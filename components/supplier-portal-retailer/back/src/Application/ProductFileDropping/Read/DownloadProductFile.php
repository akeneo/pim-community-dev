<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read;

final class DownloadProductFile
{
    public function __construct(public string $productFileIdentifier)
    {
    }
}
