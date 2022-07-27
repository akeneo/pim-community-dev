<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read;

interface GetSupplierFilesCount
{
    public function __invoke(): int;
}
