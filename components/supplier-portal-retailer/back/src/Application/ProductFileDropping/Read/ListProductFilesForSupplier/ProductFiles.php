<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier;

final class ProductFiles
{
    public function __construct(
        public array $productFiles,
        public int $totalProductFilesCount,
        public int $totalSearchResultsCount
    ) {
    }
}
