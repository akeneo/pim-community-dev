<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile\ImportProductFile as ImportProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile\ImportProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Exception\TailoredImportLaunchError;
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
        $productFileImportConfigurationCode = $request->request->get('productFileImportConfigurationCode');
        $productFileIdentifier = $request->request->get('productFileIdentifier');

        if (null === $productFileImportConfigurationCode || null === $productFileIdentifier) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            $url = ($this->importProductFileHandler)(new ImportProductFileCommand($productFileImportConfigurationCode, $productFileIdentifier));
        } catch (ProductFileDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        } catch (TailoredImportLaunchError) {
            return new JsonResponse('tailored_import_launch_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($url, Response::HTTP_ACCEPTED);
    }
}
