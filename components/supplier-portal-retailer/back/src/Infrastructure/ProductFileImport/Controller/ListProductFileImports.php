<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports\ListProductFileImportsHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports\ListProductFileImports as ListProductFileImportsCommand;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ListProductFileImports
{
    public function __construct(private ListProductFileImportsHandler $listProductFileImportsHandler)
    {
    }

    public function __invoke(): JsonResponse
    {
        $productFileImports = ($this->listProductFileImportsHandler)(new ListProductFileImportsCommand());

        return new JsonResponse(
            array_map(
                fn (ProductFileImport $productFileImport) => $productFileImport->toArray(),
                $productFileImports,
            ),
        );
    }
}
