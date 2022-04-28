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

    public function __invoke(Request $request, string $supplierIdentifier): JsonResponse
    {
        $urlEncodedEmails = $request->query->get('emails');
        if (empty($urlEncodedEmails)) {
            return new JsonResponse([]);
        }

        $emails = \json_decode($request->get('emails'));

        return new JsonResponse(
            ($this->supplierContributorsBelongToAnotherSupplier)(
                $supplierIdentifier,
                $emails,
            )
        );
    }
}
