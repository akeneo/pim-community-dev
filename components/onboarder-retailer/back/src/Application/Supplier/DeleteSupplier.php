<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Supplier;

final class DeleteSupplier
{
    public function __construct(
        public string $identifier,
    ) {
    }
}
