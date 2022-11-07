<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\FindAllProductFileImportConfigurations;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;

final class InMemoryFindAllProductFileImportConfigurations implements FindAllProductFileImportConfigurations
{
    public function __construct(private array $productFileImportConfigurations = [])
    {
    }

    public function __invoke(): array
    {
        return $this->productFileImportConfigurations;
    }

    public function add(ProductFileImportConfiguration $productFileImport): void
    {
        $this->productFileImportConfigurations[] = $productFileImport;
    }
}
