<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\FindAllProductFileImportConfigurations;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;

class ListProductFileImportConfigurationsHandler
{
    public function __construct(private FindAllProductFileImportConfigurations $findAllProductFileImports)
    {
    }

    /**
     * @return array<ProductFileImportConfiguration>
     */
    public function __invoke(ListProductFileImportConfigurations $fileImports): array
    {
        return ($this->findAllProductFileImports)();
    }
}
