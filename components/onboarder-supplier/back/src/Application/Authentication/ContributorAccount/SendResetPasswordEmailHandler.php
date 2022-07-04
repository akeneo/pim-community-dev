<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Domain\Mailer\SendEmail;
use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\Email;

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
