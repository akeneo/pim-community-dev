<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read;

interface GetAllSupplierFilesCount
{
    public function __invoke(): int;
}
