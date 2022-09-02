<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile;

final class DownloadProductFileQuery
{
    public function __construct(public string $productFileIdentifier, public string $contributorEmail)
    {
    }
}
