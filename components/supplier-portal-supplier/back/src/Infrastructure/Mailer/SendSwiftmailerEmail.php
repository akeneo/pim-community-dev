<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer;

use Swift_Mailer;

class SendSwiftmailerEmail
{
    public function __construct(private Swift_Mailer $mailer)
    {
    }

    public function __invoke(SwiftEmail $email): void
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
