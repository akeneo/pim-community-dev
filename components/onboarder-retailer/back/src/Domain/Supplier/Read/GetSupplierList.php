<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetSupplierList
{
    public const NUMBER_OF_SUPPLIERS_PER_PAGE = 50;

    public function __invoke(int $page = 1, string $search = ''): array;
}
