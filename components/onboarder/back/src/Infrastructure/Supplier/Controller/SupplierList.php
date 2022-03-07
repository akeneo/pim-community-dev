<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliers;
use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliersHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Supplier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SupplierList
{
    public function __construct(private GetSuppliersHandler $getSuppliersHandler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);

        $suppliers = ($this->getSuppliersHandler)(new GetSuppliers($page, $search));

        return new JsonResponse(
            array_map(function (Supplier $supplier) {
                return $supplier->toArray();
            }, $suppliers)
        );
    }
}
