<?php

namespace Pim\Bundle\NotificationBundle\Email;

/**
 * Notify by email
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements Notifier
{
    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var string  */
    protected $senderEmail;

    /** @var \Swift_Transport */
    protected $transport;

    /**
     * @param \Swift_Mailer    $mailer
     * @param \Swift_Transport $transport
     * @param string           $senderEmail
     */
    public function __construct(
        \Swift_Mailer $mailer,
        \Swift_Transport $transport,
        $senderEmail
    ) {
        $this->mailer       = $mailer;
        $this->transport    = $transport;
        $this->senderEmail  = $senderEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(array $users, $subject, $txtBody, $htmlBody = null, array $options = [])
    {
        foreach ($users as $user) {
            $message = $this->mailer->createMessage();
            $message->setSubject('Asset expiration')
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
