<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface as MailNotification;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;
use Twig\Environment;

/**
 * Notify Job execution result by mail
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class MailNotifier implements Notifier
{
    private const SUCCESS_SUBJECT_TEMPLATE = 'Akeneo successfully completed your "%s" job';
    private const FAILURE_SUBJECT_TEMPLATE = 'Akeneo completed your "%s" job with errors';

    private array $recipientEmails = [];

    public function __construct(
        private LoggerInterface $logger,
        private TokenStorageInterface $tokenStorage,
        private Environment $twig,
        private MailNotification $mailer,
    ) {
    }

    public function notify(JobExecution $jobExecution): void
    {
        $emailsToNotify = $this->getEmailsToNotify();

        $this->sendMail($jobExecution, $emailsToNotify);
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipientEmails = $recipients;
    }

    private function getEmailsToNotify(): array
    {
        $emailsToNotify = $this->recipientEmails;

        if (0 === count($emailsToNotify)) {
            $authenticatedUserEmail = $this->tokenStorage->getToken()?->getUser()?->getEmail();
            if (null !== $authenticatedUserEmail) {
                $emailsToNotify[] = $authenticatedUserEmail;
            }
        }

        return array_unique($emailsToNotify);
    }

    private function sendMail(JobExecution $jobExecution, array $emails): void
    {
        $parameters = [
            'jobExecution' => $jobExecution,
        ];

        $subject = $this->getSubject($jobExecution);

        try {
            $txtBody = $this->twig->render('@AkeneoBatch/Email/notification.txt.twig', $parameters);
            $htmlBody = $this->twig->render('@AkeneoBatch/Email/notification.html.twig', $parameters);
            $this->mailer->notify($emails, $subject, $txtBody, $htmlBody);
        } catch (Throwable $exception) {
            $this->logger->error(
                MailNotifier::class . ' - Unable to send email : ' . $exception->getMessage(),
                ['Exception' => $exception]
            );
        }
    }

    private function getSubject(JobExecution $jobExecution): string
    {
        $subjectTemplate = $jobExecution->getStatus()->isUnsuccessful() ? self::FAILURE_SUBJECT_TEMPLATE : self::SUCCESS_SUBJECT_TEMPLATE;
        $jobLabel = $jobExecution->getJobInstance()->getLabel();

        return sprintf($subjectTemplate, $jobLabel);
    }
}
