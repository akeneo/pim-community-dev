<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\LaunchProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\LaunchProductFileImportResult;

final class InMemoryLaunchTailoredImport implements LaunchProductFileImport
{
    public function __invoke(string $productFileImportConfigurationCode, string $filename, $productFileResource): LaunchProductFileImportResult
    {
        return new LaunchProductFileImportResult(666, 'http://www.google.fr');
    }
}
