<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write;

interface LaunchProductFileImport
{
    //@phpstan-ignore-next-line
    public function __invoke(string $productFileImportConfigurationCode, string $filename, $productFileResource): LaunchProductFileImportResult;
}
