<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model\Supplier;
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
        $supplierIdentifier = (string) Uuid::uuid4();
        $supplierCode = $request->get('code');
        $supplierLabel = $request->get('label');

        ($this->createSupplierHandler)(new CreateSupplier(
            $supplierIdentifier,
            $supplierCode,
            $supplierLabel
        ));

        return new JsonResponse(
            (new Supplier(
                $supplierIdentifier,
                $supplierCode,
                $supplierLabel,
            ))->toArray(),
            Response::HTTP_CREATED
        );
    }
}
