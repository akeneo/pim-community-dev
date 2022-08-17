<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping;

final class DownloadProductFile
{
    public function __construct(public string $productFileIdentifier)
    {
    }
}
