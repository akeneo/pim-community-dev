<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithComments;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetProductFile
{
    public function __construct(private GetProductFileWithComments $getProductFileWithComments)
    {
    }

    public function __invoke(string $productFileIdentifier): Response
    {
        $productFile = ($this->getProductFileWithComments)($productFileIdentifier);

        if (null === $productFile) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($productFile->toArray(), Response::HTTP_OK);
    }
}
