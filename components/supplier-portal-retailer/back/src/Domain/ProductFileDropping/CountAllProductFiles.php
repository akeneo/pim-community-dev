<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface CountAllProductFiles
{
    public function __invoke(): int;
}
