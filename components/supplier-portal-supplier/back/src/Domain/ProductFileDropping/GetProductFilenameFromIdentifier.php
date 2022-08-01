<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;

interface GetProductFilenameFromIdentifier
{
    public function __invoke(Identifier $identifier): ?Filename;
}
