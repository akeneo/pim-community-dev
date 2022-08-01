<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;

interface GetProductFilePath
{
    public function __invoke(Identifier $productFileIdentifier): ?Path;
}
