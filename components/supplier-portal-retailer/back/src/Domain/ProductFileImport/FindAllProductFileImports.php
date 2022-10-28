<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileImport;

interface FindAllProductFileImports
{
    /**
     * @return array<ProductFileImport>
     */
    public function __invoke(): array;
}
