<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;

interface GetProductFilePathAndFileName
{
    public function __invoke(string $productFileIdentifier): ?ProductFilePathAndFileName;
}
