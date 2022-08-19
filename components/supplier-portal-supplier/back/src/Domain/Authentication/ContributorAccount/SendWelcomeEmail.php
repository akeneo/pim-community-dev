<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Psr\Log\LoggerInterface;

class SendWelcomeEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildWelcomeEmail $buildWelcomeEmail,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $recipientEmail, string $accessToken): void
    {
        $email = ($this->buildWelcomeEmail)($recipientEmail, $accessToken);

        ($this->sendEmail)($email);

        $this->logger->info(sprintf('A welcome email has been sent to "%s"', $email->to));
    }
}
