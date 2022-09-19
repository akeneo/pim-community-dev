<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;

interface GetProductFileWithCommentsForSupplier
{
    public function __invoke(string $productFileIdentifier): ?ProductFile;
}
