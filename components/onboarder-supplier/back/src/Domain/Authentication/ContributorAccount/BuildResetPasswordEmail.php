<?php

namespace Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\EmailContent;

interface BuildResetPasswordEmail
{
    public function __invoke(string $email, string $accessToken): EmailContent;
}
