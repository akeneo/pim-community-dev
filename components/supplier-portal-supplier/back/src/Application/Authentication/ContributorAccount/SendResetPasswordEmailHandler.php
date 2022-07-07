<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;

class SendResetPasswordEmailHandler
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildResetPasswordEmail $buildResetPasswordEmail,
    ) {
    }

    public function __invoke(SendResetPasswordEmail $sendResetPasswordEmail): void
    {
        $email = ($this->buildResetPasswordEmail)(
            $sendResetPasswordEmail->email,
            $sendResetPasswordEmail->accessToken
        );

        ($this->sendEmail)($email);
    }
}
