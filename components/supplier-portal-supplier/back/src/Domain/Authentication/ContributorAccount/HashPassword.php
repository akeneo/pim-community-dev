<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

interface HashPassword
{
    public function __invoke(string $email, string $plainTextPassword): string;
}
