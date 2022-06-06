<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SupplierCreate
{
    public function __construct(private CreateSupplierHandler $createSupplierHandler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $supplierIdentifier = Uuid::uuid4()->toString();
        $supplierCode = $request->get('code');
        $supplierLabel = $request->get('label');

        try {
            ($this->createSupplierHandler)(new CreateSupplier(
                $supplierIdentifier,
                $supplierCode,
                $supplierLabel,
                [],
            ));
        } catch (SupplierAlreadyExistsException) {
            return new JsonResponse(null, Response::HTTP_CONFLICT);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
