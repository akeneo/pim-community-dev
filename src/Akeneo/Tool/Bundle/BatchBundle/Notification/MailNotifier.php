<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Email\SenderAddress;
use Swift_Mailer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * Notify Job execution result by mail
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements Notifier
{
    protected BatchLogHandler $logger;
    protected TokenStorageInterface $tokenStorage;
    protected Environment $twig;
    protected Swift_Mailer $mailer;
    protected string $mailerUrl;
    protected string $recipientEmail;

    public function __construct(
        BatchLogHandler $logger,
        TokenStorageInterface $tokenStorage,
        Environment $twig,
        Swift_Mailer $mailer,
        string $mailerUrl
    ) {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->mailerUrl = $mailerUrl;
    }

    public function setRecipientEmail(string $recipientEmail): self
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(JobExecution $jobExecution): void
    {
        if (null === $email = $this->getEmail()) {
            return;
        }

        $parameters = [
            'jobExecution' => $jobExecution,
            'log' => $this->logger->getFilename(),
        ];

        $txtBody = $this->twig->render('AkeneoBatchBundle:Mails:notification.txt.twig', $parameters);
        $htmlBody = $this->twig->render('AkeneoBatchBundle:Mails:notification.html.twig', $parameters);

        $message = $this->mailer->createMessage();
        $message->setSubject('Job has been executed');
        $message->setFrom((string)SenderAddress::fromMailerUrl($this->mailerUrl));
        $message->setTo($email);
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Get the current authenticated user
     */
    private function getEmail(): ?string
    {
        if ($this->recipientEmail) {
            return $this->recipientEmail;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user->getEmail();
    }
}
