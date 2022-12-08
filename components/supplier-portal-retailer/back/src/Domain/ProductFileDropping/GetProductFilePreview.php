<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePreview;

interface GetProductFilePreview
{
    public function __invoke(string $productFilePath, string $productFileName): ProductFilePreview;
}
