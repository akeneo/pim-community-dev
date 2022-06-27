<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write;

interface HashPassword
{
    public function __invoke(string $email, string $plainTextPassword): string;
}
