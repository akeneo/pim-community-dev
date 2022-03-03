<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

final class CreateSupplier
{
    public function __construct(
        public string $identifier,
        public string $code,
        public string $label
    ) {
    }
}
