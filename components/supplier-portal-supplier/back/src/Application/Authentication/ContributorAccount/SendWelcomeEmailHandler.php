<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;

class SendWelcomeEmailHandler
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildWelcomeEmail $buildWelcomeEmail,
    ) {
    }

    public function __invoke(string $recipientEmail, string $accessToken): void
    {
        $email = ($this->buildWelcomeEmail)($recipientEmail, $accessToken);

        ($this->sendEmail)($email);
    }
}
