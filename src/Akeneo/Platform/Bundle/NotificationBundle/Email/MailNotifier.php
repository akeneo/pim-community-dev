<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Email;

/**
 * Notify by email
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MailNotifier
{
    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var string */
    protected $senderEmail;

    /**
     * @param \Swift_Mailer $mailer
     * @param string        $senderEmail
     */
    public function __construct(\Swift_Mailer $mailer, $senderEmail)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(array $users, $subject, $txtBody, $htmlBody = null, array $options = [])
    {
        foreach ($users as $user) {
            $message = $this->mailer->createMessage();
            $message->setSubject($subject)
                ->setFrom($this->senderEmail)
                ->setTo($user->getEmail())
                ->setCharset('UTF-8')
                ->setContentType('text/html')
                ->setBody($txtBody, 'text/plain')
                ->addPart($htmlBody, 'text/html');

            $this->mailer->send($message);
        }
    }
}
