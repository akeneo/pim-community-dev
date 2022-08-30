<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

final class DownloadProductFileForSupplier
{
    public function __construct(public string $productFileIdentifier, public string $contributorEmail)
    {
    }
}
