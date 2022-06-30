<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\BuildResetPasswordEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\Email;

final class SendResetPasswordEmailHandler
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildResetPasswordEmail $buildResetPasswordEmail,
    ) {
    }

    public function __invoke(SendResetPasswordEmail $sendResetPasswordEmail)
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
