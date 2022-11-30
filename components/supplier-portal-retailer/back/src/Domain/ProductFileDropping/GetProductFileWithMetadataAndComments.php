<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithMetadataAndComments;

interface GetProductFileWithMetadataAndComments
{
    public function __invoke(string $productFileIdentifier): ?ProductFileWithMetadataAndComments;
}
