<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write;

final class CreateSupplier
{
    public function __construct(
        public string $code,
        public string $label,
        public array $contributorEmails,
    ) {
    }
}
