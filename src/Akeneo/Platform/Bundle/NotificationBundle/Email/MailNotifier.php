<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Email;

use Akeneo\Tool\Component\Email\SenderAddress;
use Swift_Mailer;

/**
 * Notify by email
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements MailNotifierInterface
{
    protected object $message;

    public function __construct(
        protected Swift_Mailer $mailer,
        protected string       $mailerUrl
    ) {
    }

    public function notify(array $users, $subject, $txtBody, $htmlBody = null, array $options = [])
    {
        foreach ($users as $user) {
            $this->send($user->getEmail(), $subject, $txtBody, $htmlBody);
        }
    }

    public function notifyByEmails(array $emails, string $subject, string $txtBody, $htmlBody = null, array $options = [])
    {
        foreach ($emails as $email) {
            $this->send($email, $subject, $txtBody, $htmlBody);
        }
    }

    private function send($email, $subject, $txtBody, $htmlBody)
    {
        $message = $this->mailer->createMessage();
        $message->setSubject($subject)
            ->setFrom((string)SenderAddress::fromMailerUrl($this->mailerUrl))
            ->setTo($email)
            ->setCharset('UTF-8')
            ->setContentType('text/html')
            ->setBody($txtBody, 'text/plain')
            ->addPart($htmlBody, 'text/html');

        $this->mailer->send($message);
    }
}
