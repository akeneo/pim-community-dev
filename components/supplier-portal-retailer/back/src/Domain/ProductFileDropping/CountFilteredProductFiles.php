<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface CountFilteredProductFiles
{
    public function __invoke(string $search = ''): int;
}
