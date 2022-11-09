<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;

interface Repository
{
    public function save(Supplier $supplier): void;
    public function delete(Identifier $identifier): void;
    public function find(Identifier $identifier): ?Supplier;
}
