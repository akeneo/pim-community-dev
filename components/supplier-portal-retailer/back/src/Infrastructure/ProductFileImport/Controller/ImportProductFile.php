<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile\ImportProductFile as ImportProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile\ImportProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Exception\ProductFileDoesNotExist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImportProductFile
{
    public function __construct(private readonly ImportProductFileHandler $importProductFileHandler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $productFileImportConfigurationCode = $request->request->get('productFileImportConfiguration');
        $productFileIdentifier = $request->request->get('productFileIdentifier');

        try {
            ($this->importProductFileHandler)(new ImportProductFileCommand($productFileImportConfigurationCode, $productFileIdentifier));
        } catch (ProductFileDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
