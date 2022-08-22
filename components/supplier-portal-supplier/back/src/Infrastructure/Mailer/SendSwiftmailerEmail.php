<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer;

use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Swift_Mailer;

final class SendSwiftmailerEmail implements SendEmail
{
    public function __construct(private Swift_Mailer $mailer)
    {
    }

    public function __invoke(Email $email): void
    {
        $message = $this->mailer->createMessage();

        $message->setSubject($email->subject)
            ->setFrom($email->from)
            ->setTo($email->to)
            ->setCharset('UTF-8')
            ->setContentType('text/html')
            ->setBody($email->htmlContent, 'text/html')
            ->addPart($email->txtContent, 'text/plain');

        foreach ($email->attachments as $attachment) {
            $message->embed($attachment);
        }

        $this->mailer->send($message);
    }
}
