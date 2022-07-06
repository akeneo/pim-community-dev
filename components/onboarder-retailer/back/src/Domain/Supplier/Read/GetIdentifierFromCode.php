<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;

interface GetIdentifierFromCode
{
    public function __invoke(Code $code): ?string;
}
