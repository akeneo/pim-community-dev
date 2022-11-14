<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;

final class InMemoryStreamStoredProductFile implements StreamStoredProductFile
{
    //@phpstan-ignore-next-line
    public function __invoke(string $productFilePath)
    {
        return fopen('php://memory', 'r');
    }
}
