<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Controller;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\DeleteSupplier\DeleteSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\DeleteSupplier\DeleteSupplierHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SupplierDelete
{
    public function __construct(private DeleteSupplierHandler $deleteSupplierHandler)
    {
    }

    public function __invoke(string $identifier): JsonResponse
    {
        ($this->deleteSupplierHandler)(new DeleteSupplier($identifier));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
