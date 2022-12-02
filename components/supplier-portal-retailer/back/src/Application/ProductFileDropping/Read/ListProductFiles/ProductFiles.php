<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles;

final class ProductFiles
{
    public function __construct(
        public readonly array $productFiles,
        public readonly int $totalProductFilesCount,
        public readonly int $searchResultsCount,
    ) {
    }
}
