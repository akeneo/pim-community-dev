<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount;

final class UpdatePassword
{
    public function __construct(
        public string $contributorAccountIdentifier,
        public string $plainTextPassword,
    ) {
    }
}
