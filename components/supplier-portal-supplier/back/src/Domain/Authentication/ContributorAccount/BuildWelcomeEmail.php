<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\EmailContent;

interface BuildWelcomeEmail
{
    public function __invoke(string $accessToken, string $email): EmailContent;
}
