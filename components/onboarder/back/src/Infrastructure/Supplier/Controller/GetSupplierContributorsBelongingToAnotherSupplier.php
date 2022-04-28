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

        try {
            $emails = \json_decode($urlEncodedEmails, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse([]);
        }

        return new JsonResponse(
            ($this->supplierContributorsBelongToAnotherSupplier)(
                $supplierIdentifier,
                $emails,
            ),
        );
    }
}
