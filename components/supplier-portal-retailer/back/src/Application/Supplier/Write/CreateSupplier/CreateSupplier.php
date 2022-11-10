<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\CreateSupplier;

final class CreateSupplier
{
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly array $contributorEmails,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
