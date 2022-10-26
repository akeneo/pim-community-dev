<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\MarkCommentsAsRead as MarkCommentsAsReadAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\MarkCommentsAsReadCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MarkCommentsAsRead
{
    public function __construct(private MarkCommentsAsReadAPI $markCommentsAsRead)
    {
    }

    public function __invoke(string $productFileIdentifier): JsonResponse
    {
        try {
            ($this->markCommentsAsRead)(new MarkCommentsAsReadCommand($productFileIdentifier, new \DateTimeImmutable()));
        } catch (ProductFileDoesNotExist) {
            return new JsonResponse('product_file_does_not_exist', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
