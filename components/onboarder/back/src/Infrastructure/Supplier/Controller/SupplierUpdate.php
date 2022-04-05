<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SupplierUpdate
{
    public function __construct(private UpdateSupplierHandler $updateSupplierHandler)
    {
    }

    public function __invoke(Request $request, string $identifier): JsonResponse
    {
        $contributorEmails = $request->request->get('contributorEmails');
        $supplierLabel = $request->request->get('label');

        ($this->updateSupplierHandler)(new UpdateSupplier($identifier, $supplierLabel, $contributorEmails));
    }
}
