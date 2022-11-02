<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface CountProductFiles
{
    public function __invoke(): int;
}
