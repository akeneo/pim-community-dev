<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\BuildWelcomeEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\Email;

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
            "You've received an invitation to contribute to onboarder",
            $emailContent->htmlContent,
            $emailContent->textContent,
            'noreply@akeneo.com',
            $command->email,
        );
        ($this->sendEmail)($email);
    }
}
