<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\Email;

interface BuildResetPasswordEmail
{
    public function __invoke(string $email, string $accessToken): Email;
}
