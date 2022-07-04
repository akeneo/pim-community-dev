<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Domain\Mailer\SendEmail;
use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\Email;

class SendWelcomeEmailHandler
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildWelcomeEmail $buildWelcomeEmail,
    ) {
    }

    public function __invoke(SendWelcomeEmail $command): void
    {
        $emailContent = ($this->buildWelcomeEmail)($command->accessToken, $command->email);

        $email = new Email(
            "You've received an invitation to contribute to Akeneo Supplier Portal",
            $emailContent->htmlContent,
            $emailContent->textContent,
            'noreply@akeneo.com',
            $command->email,
        );
        ($this->sendEmail)($email);
    }
}
