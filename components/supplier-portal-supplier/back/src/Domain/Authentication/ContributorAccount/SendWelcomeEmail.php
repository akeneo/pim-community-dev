<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

interface SendWelcomeEmail
{
    public function __invoke(string $email, string $accessToken): void;
}
