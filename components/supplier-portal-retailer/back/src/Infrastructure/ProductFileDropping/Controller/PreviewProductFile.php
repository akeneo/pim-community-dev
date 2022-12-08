<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\PreviewProductFile\PreviewProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\PreviewProductFile\PreviewProductFile as PreviewProductFileQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class PreviewProductFile
{
    public function __construct(private readonly PreviewProductFileHandler $previewProductFileHandler)
    {
    }

    public function __invoke(string $productFileIdentifier): JsonResponse
    {
        try {
            $productFilePreview = ($this->previewProductFileHandler)(new PreviewProductFileQuery($productFileIdentifier));
        } catch (ProductFileDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        } catch (UnableToReadProductFile) {
            return new JsonResponse(null, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new JsonResponse($productFilePreview);
    }
}
