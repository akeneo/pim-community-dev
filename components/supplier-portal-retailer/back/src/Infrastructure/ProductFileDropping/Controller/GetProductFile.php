<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithMetadataAndComments;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetProductFile
{
    public function __construct(private GetProductFileWithMetadataAndComments $getProductFileWithMetadataAndComments)
    {
    }

    public function __invoke(string $productFileIdentifier): Response
    {
        $productFile = ($this->getProductFileWithMetadataAndComments)($productFileIdentifier);

        if (null === $productFile) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($productFile->toArray(), Response::HTTP_OK);
    }
}
