<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;

class SendGoodbyeEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildGoodbyeEmail $buildGoodbyeEmail,
    ) {
    }

    public function __invoke(string $recipientEmail): void
    {
        $email = ($this->buildGoodbyeEmail)($recipientEmail);

        ($this->sendEmail)($email);
    }
}
