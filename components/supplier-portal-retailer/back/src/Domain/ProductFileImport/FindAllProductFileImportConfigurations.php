<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;

interface FindAllProductFileImportConfigurations
{
    /**
     * @return array<ProductFileImportConfiguration>
     */
    public function __invoke(): array;
}
