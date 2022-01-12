<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifier as MailNotification;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;
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
        protected LoggerInterface       $logger,
        protected TokenStorageInterface $tokenStorage,
        protected Environment           $twig,
        protected MailNotification      $mailer,
        protected string                $imagePath
    ) {
    }

    public function setRecipientEmail(string $recipientEmail): self
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    public function notify(JobExecution $jobExecution)
    {
        if (null === $email = $this->getEmail()) {
            return;
        }

        // TODO:
        //  - manage images
        //  - need message instance
        // Add embedded images
//        $cidLogo = $message->embed(Swift_Image::fromPath($this->imagePath . '/email_logo.png')
//            ->setDisposition('inline'));
//        $cidSignature = $message->embed(Swift_Image::fromPath($this->imagePath . '/email_signature.png')
//            ->setDisposition('inline'));

        $parameters = [
            'jobExecution' => $jobExecution,
            'email' => $email
//            'images' => [
//                'logo' => $cidLogo,
//                'signature' => $cidSignature
//            ]
        ];

        try {
            $txtBody = $this->twig->render('@PimNotification/Email/notification.txt.twig', $parameters);
            $htmlBody = $this->twig->render('@PimNotification/Email/notification.html.twig', $parameters);
            $this->mailer->notifyByEmails([$email], 'Job has been executed', $txtBody, $htmlBody);
        } catch (Throwable $exception) {
            $this->logger->error(
                MailNotifier::class . ' Unable to send email due to : ' . $exception->getMessage(),
                ['Exception' => $exception]
            );
            return;
        }
    }

    /**
     * Get the current authenticated user
     *
     * @return null|string
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
