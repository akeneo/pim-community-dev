<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\FindAllProductFileImports;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;

final class InMemoryFindAllProductFileImportProfiles implements FindAllProductFileImports
{
    public function __construct(private array $productFileImports = [])
    {
    }

    public function __invoke(): array
    {
        return $this->productFileImports;
    }

    public function add(ProductFileImport $productFileImport): void
    {
        $this->productFileImports[] = $productFileImport;
    }
}
