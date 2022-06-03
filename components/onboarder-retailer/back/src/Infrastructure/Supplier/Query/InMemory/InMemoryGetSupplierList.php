<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\GetSupplierList;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\Model\SupplierWithContributorCount;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as SupplierRepository;

class InMemoryGetSupplierList implements GetSupplierList
{
    public function __construct(private SupplierRepository $repository)
    {
    }

    public function __invoke(int $page = 1, string $search = ''): array
    {
        $suppliers = $this->repository->findAll();

        $suppliers = '' === $search ?
            $this->paginateSuppliers($suppliers, $page) :
            $this->paginateSuppliers($this->filterSuppliers($suppliers, $search), $page)
        ;
        $this->sortByLabel($suppliers);

        return $this->buildReadModels($suppliers);
    }

    private function paginateSuppliers(array $suppliers, int $page): array
    {
        return array_slice(
            $suppliers,
            self::NUMBER_OF_SUPPLIERS_PER_PAGE * ($page - 1),
            self::NUMBER_OF_SUPPLIERS_PER_PAGE,
        );
    }

    private function filterSuppliers(array $suppliers, string $search): array
    {
        return array_filter(
            $suppliers,
            fn (Supplier $supplier) =>
                1 <= strpos(strtolower($supplier->label()), strtolower($search)),
        );
    }

    private function sortByLabel(array &$suppliers): void
    {
        uasort(
            $suppliers,
            function (Supplier $supplier1, Supplier $supplier2) {
                return strcmp($supplier1->label(), $supplier2->label());
            },
        );
    }

    private function buildReadModels(array $suppliers): array
    {
        return array_map(function (Supplier $supplier) {
            return new SupplierWithContributorCount($supplier->identifier(), $supplier->code(), $supplier->label(), count($supplier->contributors()));
        }, $suppliers);
    }
}
