<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFile;

interface GetProductFiles
{
    /**
     * @return SupplierFile[]
     */
    public function __invoke(string $contributorEmail): array;
}
