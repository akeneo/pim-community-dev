<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Email\SenderAddress;
use Swift_Image;
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
    protected ?string $recipientEmail = null;

    public function __construct(
        protected BatchLogHandler       $logger,
        protected TokenStorageInterface $tokenStorage,
        protected Environment           $twig,
        protected \Swift_Mailer         $mailer,
        protected string                $mailerUrl,
        protected string                $imagePath
    ) {
    }

    /**
     * @param string $recipientEmail
     */
    public function setRecipientEmail($recipientEmail): self
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

        $message = $this->mailer->createMessage();

        // Add embedded images
        $cidLogo = $message->embed(Swift_Image::fromPath($this->imagePath . '/email_logo.png')
            ->setDisposition('inline'));
        $cidSignature = $message->embed(Swift_Image::fromPath($this->imagePath . '/email_signature.png')
            ->setDisposition('inline'));

        $parameters = [
            'jobExecution' => $jobExecution,
            'log' => $this->logger->getFilename(),
            'email' => $email,
            'images' => [
                'logo' => $cidLogo,
                'signature' => $cidSignature
            ]
        ];

        $txtBody = $this->twig->render('@AkeneoBatch/Mails/notification.txt.twig', $parameters);
        $htmlBody = $this->twig->render('@AkeneoBatch/Mails/notification.html.twig', $parameters);

        // Prepare message
        $message->setSubject('Job has been executed');
        $message->setFrom((string)SenderAddress::fromMailerUrl($this->mailerUrl));
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
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user->getEmail();
    }
}
