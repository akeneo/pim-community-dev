<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\UpdateSupplier;

final class UpdateSupplier
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $label,
        public readonly array $contributorEmails,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
