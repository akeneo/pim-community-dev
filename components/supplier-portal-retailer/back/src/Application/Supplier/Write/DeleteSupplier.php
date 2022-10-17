<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write;

final class DeleteSupplier
{
    public function __construct(
        public string $identifier,
    ) {
    }
}
