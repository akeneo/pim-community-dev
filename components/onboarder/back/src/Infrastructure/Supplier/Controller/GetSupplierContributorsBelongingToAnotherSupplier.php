<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class GetSupplierContributorsBelongingToAnotherSupplier
{
    public function __construct(private SupplierContributorsBelongingToAnotherSupplier $supplierContributorsBelongToAnotherSupplier)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse(
            ($this->supplierContributorsBelongToAnotherSupplier)(
                $request->get('supplierIdentifier'),
                \json_decode($request->get('emails'))
            )
        );
    }
}
