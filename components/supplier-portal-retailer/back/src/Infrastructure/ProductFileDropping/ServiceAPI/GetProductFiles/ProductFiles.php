<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

final class ProductFiles
{
    public function __construct(
        public array $productFiles,
        public int $numberTotalOfProductFiles,
    ) {
    }
}
