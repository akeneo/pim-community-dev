<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Psr\Log\LoggerInterface;

class SendResetPasswordEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildResetPasswordEmail $buildResetPasswordEmail,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $recipientEmail, string $accessToken): void
    {
        $email = ($this->buildResetPasswordEmail)($recipientEmail, $accessToken);

        ($this->sendEmail)($email);

        $this->logger->info(sprintf('A reset password email has been sent to "%s"', $email->to));
    }
}
