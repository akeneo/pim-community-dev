<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer;

use Akeneo\SupplierPortal\Supplier\Domain\Email;
use Akeneo\SupplierPortal\Supplier\Domain\SendEmail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendSymfonyEmail implements SendEmail
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(Email $email): void
    {
        $emailMessage = (new TemplatedEmail())
            ->subject($email->subject)
            ->to($email->to)
            ->htmlTemplate($email->htmlTemplate)
            ->textTemplate($email->textTemplate)
            ->context($email->templateContext)
            ->embedFromPath($email->embeddedLogoPath, 'logo');

        $this->mailer->send($emailMessage);
    }
}
