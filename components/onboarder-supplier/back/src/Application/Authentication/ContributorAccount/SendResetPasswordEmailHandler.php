<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\Email;

class SendResetPasswordEmailHandler
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildResetPasswordEmail $buildResetPasswordEmail,
    ) {
    }

    public function __invoke(SendResetPasswordEmail $sendResetPasswordEmail): void
    {
        $resetPasswordEmailContent = ($this->buildResetPasswordEmail)(
            $sendResetPasswordEmail->email,
            $sendResetPasswordEmail->accessToken
        );

        $email = new Email(
            'Reset your password',
            $resetPasswordEmailContent->htmlContent,
            $resetPasswordEmailContent->textContent,
            'noreply@akeneo.com',
            $sendResetPasswordEmail->email,
        );

        ($this->sendEmail)($email);
    }
}
