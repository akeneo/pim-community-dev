<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\EmailContent;

interface BuildWelcomeEmail
{
    public function __invoke(string $accessToken, string $email): EmailContent;
}
