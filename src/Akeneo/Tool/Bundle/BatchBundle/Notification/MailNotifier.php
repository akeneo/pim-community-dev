<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface as MailNotification;
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
    protected array $recipientEmails = [];

    public function __construct(
        protected LoggerInterface       $logger,
        protected TokenStorageInterface $tokenStorage,
        protected Environment           $twig,
        protected MailNotification      $mailer
    ) {
    }

    public function setRecipientEmails(array $recipientEmails): self
    {
        $this->recipientEmails = $recipientEmails;

        return $this;
    }

    public function notify(JobExecution $jobExecution)
    {
        $emails = $this->getEmail();
        if (empty($emails)) {
            return;
        }


        try {
            foreach ($emails as $email) {
                $parameters = [
                    'jobExecution' => $jobExecution,
                    'email' => $email
                ];

                $txtBody = $this->twig->render('@AkeneoBatch/Email/notification.txt.twig', $parameters);
                $htmlBody = $this->twig->render('@AkeneoBatch/Email/notification.html.twig', $parameters);
                $this->mailer->notifyByEmail($email, 'Job has been executed', $txtBody, $htmlBody);
            }
        } catch (Throwable $exception) {
            $this->logger->error(
                MailNotifier::class . ' - Unable to send email : ' . $exception->getMessage(),
                ['Exception' => $exception]
            );
            return;
        }
    }

    /**
     * Get the current authenticated user
     */
    private function getEmail(): array
    {
        if ($this->recipientEmails) {
            return $this->recipientEmails;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return [];
        }

        if (!is_object($user = $token->getUser())) {
            return [];
        }

        return [$user->getEmail()];
    }
}
