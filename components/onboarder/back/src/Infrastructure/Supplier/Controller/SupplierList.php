<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliers;
use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliersHandler;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierCount;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierList;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Supplier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SupplierList
{
    public function __construct(
        private GetSuppliersHandler $getSuppliersHandler,
        private GetSupplierCount $getSupplierCount,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);

        $suppliers = ($this->getSuppliersHandler)(new GetSuppliers($page, $search));

        return new JsonResponse([
            'suppliers' => array_map(
                fn (Supplier $supplier) => $supplier->toArray(),
                $suppliers
            ),
            'total' => ($this->getSupplierCount)($search), // @todo Query count suppliers
            'items_per_page' => GetSupplierList::NUMBER_OF_SUPPLIERS_PER_PAGE
        ]);
    }
}
