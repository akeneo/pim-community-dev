<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

interface SendResetPasswordEmail
{
    public function __invoke(string $email, string $accessToken): void;
}
