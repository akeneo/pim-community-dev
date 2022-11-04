<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports\ListProductFileImportConfigurationsHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports\ListProductFileImportConfigurations as ListProductFileImportConfigurationsCommand;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ListProductFileImportConfigurations
{
    public function __construct(private ListProductFileImportConfigurationsHandler $listProductFileImportConfigurationsHandler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $productFileImports = ($this->listProductFileImportConfigurationsHandler)(new ListProductFileImportConfigurationsCommand());

        return new JsonResponse(
            array_map(
                fn (ProductFileImportConfiguration $productFileImportConfiguration) => $productFileImportConfiguration->toArray(),
                $productFileImports,
            ),
        );
    }
}
