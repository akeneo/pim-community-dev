<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\Exception\ProductFileNotFound;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFile\GetProductFile as GetProductFileServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFile\GetProductFileQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetProductFile
{
    public function __construct(private GetProductFileServiceAPI $getProductFile)
    {
    }

    public function __invoke(string $productFileIdentifier): JsonResponse
    {
        try {
            $productFile = ($this->getProductFile)(new GetProductFileQuery($productFileIdentifier));
        } catch (ProductFileNotFound) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($productFile->toArray());
    }
}
