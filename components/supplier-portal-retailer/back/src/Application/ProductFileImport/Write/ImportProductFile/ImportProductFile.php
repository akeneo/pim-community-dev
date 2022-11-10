<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile;

final class ImportProductFile
{
    public function __construct(public readonly string $importProductFileConfigurationCode, public readonly string $productFileIdentifier)
    {
    }
}
