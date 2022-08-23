<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write;

interface DeleteProductFilesFromPaths
{
    public function __invoke(array $productFilePaths): void;
}
