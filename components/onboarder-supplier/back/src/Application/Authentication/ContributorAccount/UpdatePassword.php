<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount;

final class UpdatePassword
{
    public function __construct(
        public string $contributorAccountIdentifier,
        public string $plainTextPassword,
    ) {
    }
}
