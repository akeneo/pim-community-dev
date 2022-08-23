<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;

interface GetProductFilePathAndFileName
{
    public function __invoke(Identifier $productFileIdentifier, string $contributorEmail): ?ProductFilePathAndFileName;
}
