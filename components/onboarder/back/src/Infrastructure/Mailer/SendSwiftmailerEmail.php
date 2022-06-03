<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Mailer;

use Akeneo\OnboarderSerenity\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\Email;
use Psr\Log\LoggerInterface;
use Swift_Mailer;

final class SendSwiftmailerEmail implements SendEmail
{
    public function __construct(private Swift_Mailer $mailer, private LoggerInterface $logger)
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
            ->setBody($email->txtContent, 'text/plain')
            ->addPart($email->htmlContent, 'text/html');

        $this->mailer->send($message);

        $this->logger->info(sprintf('A welcome email has been sent to "%s"', $email->to));
    }
}
