<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface DeleteProductFilesFromPaths
{
    public function __invoke(array $productFilePaths): void;
}
