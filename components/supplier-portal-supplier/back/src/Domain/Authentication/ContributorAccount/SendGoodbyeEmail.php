<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Psr\Log\LoggerInterface;

class SendGoodbyeEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private BuildGoodbyeEmail $buildGoodbyeEmail,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $recipientEmail): void
    {
        $email = ($this->buildGoodbyeEmail)($recipientEmail);

        ($this->sendEmail)($email);

        $this->logger->info(sprintf('A goodbye email has been sent to "%s"', $email->to));
    }
}
