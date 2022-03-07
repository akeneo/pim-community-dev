<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\GetSupplierList;

final class GetSuppliersHandler
{
    public function __construct(private GetSupplierList $getSupplierList)
    {
    }

    public function __invoke(GetSuppliers $getSuppliers): array
    {
        return ($this->getSupplierList)($getSuppliers->page, $getSuppliers->search);
    }
}
