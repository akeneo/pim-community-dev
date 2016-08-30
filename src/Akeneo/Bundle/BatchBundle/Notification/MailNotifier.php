<?php

namespace Akeneo\Bundle\BatchBundle\Notification;

use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Component\Batch\Model\JobExecution;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Notify Job execution result by mail
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements Notifier
{
    /**
     * @var BatchLogHandler $logger
     */
    protected $logger;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     * @var string $senderEmail
     */
    protected $senderEmail;

    /**
     * @var string $recipientEmail
     */
    protected $recipientEmail;

    /**
     * @param BatchLogHandler       $logger
     * @param TokenStorageInterface $tokenStorage
     * @param \Twig_Environment     $twig
     * @param \Swift_Mailer         $mailer
     * @param string                $senderEmail
     */
    public function __construct(
        BatchLogHandler $logger,
        TokenStorageInterface $tokenStorage,
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        $senderEmail
    ) {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
    }

    /**
     * Set the recipient email
     *
     * @param string $recipientEmail
     *
     * @return MailNotifier
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(JobExecution $jobExecution)
    {
        if (null === $email = $this->getEmail()) {
            return;
        }

        $parameters = [
            'jobExecution' => $jobExecution,
            'log'          => $this->logger->getFilename(),
        ];

        $txtBody = $this->twig->render('AkeneoBatchBundle:Mails:notification.txt.twig', $parameters);
        $htmlBody = $this->twig->render('AkeneoBatchBundle:Mails:notification.html.twig', $parameters);

        $message = $this->mailer->createMessage();
        $message->setSubject('Job has been executed');
        $message->setFrom($this->senderEmail);
        $message->setTo($email);
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Get the current authenticated user
     *
     * @return null|string
     */
    private function getEmail()
    {
        if ($this->recipientEmail) {
            return $this->recipientEmail;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user->getEmail();
    }
}
