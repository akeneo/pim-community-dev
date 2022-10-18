<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierWithContributors;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetSupplier
{
    public function __construct(private GetSupplierWithContributors $getSupplier)
    {
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $supplier = ($this->getSupplier)($identifier);

        if (null === $supplier) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($supplier->toArray());
    }
}
