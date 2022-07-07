<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;

class SendResetPasswordEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildResetPasswordEmail $buildResetPasswordEmail,
    ) {
    }

    public function __invoke(string $recipientEmail, string $accessToken): void
    {
        $email = ($this->buildResetPasswordEmail)($recipientEmail, $accessToken);

        ($this->sendEmail)($email);
    }
}
