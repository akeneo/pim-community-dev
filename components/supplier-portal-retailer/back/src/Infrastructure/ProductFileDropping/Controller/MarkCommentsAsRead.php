<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailer;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailerHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MarkCommentsAsRead
{
    public function __construct(private MarkCommentsAsReadByRetailerHandler $markCommentsAsReadByRetailerHandler)
    {
    }

    public function __invoke(string $productFileIdentifier): JsonResponse
    {
        try {
            ($this->markCommentsAsReadByRetailerHandler)(
                new MarkCommentsAsReadByRetailer($productFileIdentifier, new \DateTimeImmutable()),
            );
        } catch (ProductFileDoesNotExist) {
            return new JsonResponse('product_file_does_not_exist', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
