<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write;

final class UpdatePassword
{
    public function __construct(
        public string $contributorAccountIdentifier,
        public string $plainTextPassword,
        public bool $hasConsent,
    ) {
    }
}
