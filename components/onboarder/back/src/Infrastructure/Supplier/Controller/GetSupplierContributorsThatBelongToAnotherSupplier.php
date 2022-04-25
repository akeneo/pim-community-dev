<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongToAnotherSupplier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class GetSupplierContributorsThatBelongToAnotherSupplier
{
    public function __construct(private SupplierContributorsBelongToAnotherSupplier $supplierContributorsBelongToAnotherSupplier)
    {
    }

    public function __invoke(Request $request)
    {
        return new JsonResponse(
            ($this->supplierContributorsBelongToAnotherSupplier)(
                $request->get('supplierIdentifier'),
                $request->get('email')
            )
        );
    }
}
