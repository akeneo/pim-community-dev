<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplierHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SupplierDelete
{
    public function __construct(private DeleteSupplierHandler $deleteSupplierHandler)
    {
    }

    public function __invoke($identifier): JsonResponse
    {
        ($this->deleteSupplierHandler)(new DeleteSupplier($identifier));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
