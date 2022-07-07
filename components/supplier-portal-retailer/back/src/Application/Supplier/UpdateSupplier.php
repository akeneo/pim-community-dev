<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier;

final class UpdateSupplier
{
    public function __construct(
        public string $identifier,
        public string $label,
        public array $contributorEmails,
    ) {
    }
}
