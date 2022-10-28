<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\FindAllProductFileImports;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;

class ListProductFileImportsHandler
{
    public function __construct(private FindAllProductFileImports $fileImports)
    {
    }

    /**
     * @return array<ProductFileImport>
     */
    public function __invoke(ListProductFileImports $fileImports): array
    {
        return ($this->fileImports)();
    }
}
