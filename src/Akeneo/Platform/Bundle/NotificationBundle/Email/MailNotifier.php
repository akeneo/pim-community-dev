<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Email;

use Akeneo\Tool\Component\Email\SenderAddress;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;

/**
 * Notify by email
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements MailNotifierInterface
{
    public function __construct(
        protected Swift_Mailer $mailer,
        protected string $mailerUrl,
        private LoggerInterface $logger,
    ) {
    }

    public function notify(
        array $recipients,
        string $subject,
        string $txtBody,
        $htmlBody = null,
        array $options = []
    ): void {
        $this->send($recipients, $subject, $txtBody, $htmlBody);
    }

    private function send($recipients, $subject, $txtBody, $htmlBody): void
    {
        /** @var Swift_Mime_SimpleMessage $message */
        $message = $this->mailer->createMessage();
        $sender = (string)SenderAddress::fromMailerUrl($this->mailerUrl);
        $message->setSubject($subject)
            ->setFrom($sender)
            ->setBcc($recipients)
            ->setCharset('UTF-8')
            ->setContentType('text/html')
            ->setBody($txtBody, 'text/plain')
            ->addPart($htmlBody, 'text/html');

        $sentCount = $this->mailer->send($message);

        if (0 === $sentCount) {
            $this->logger->error(
                sprintf('Mail error from %s', $sender),
                [
                    'Subject' => $message->getSubject(),
                    'Recipients' => $message->getBcc(),
                ]
            );
        } else {
            $this->logger->info(
                sprintf('Mail sent from %s', $sender),
                [
                    'Subject' => $message->getSubject(),
                    'Recipients' => $message->getBcc(),
                ]
            );
        }
    }
}
