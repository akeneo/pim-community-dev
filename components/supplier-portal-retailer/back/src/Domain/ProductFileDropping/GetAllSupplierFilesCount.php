<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface GetAllSupplierFilesCount
{
    public function __invoke(): int;
}
