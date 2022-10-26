<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles;

final class ProductFiles
{
    public function __construct(public array $productFiles, public int $totalProductFilesCount)
    {
    }
}
