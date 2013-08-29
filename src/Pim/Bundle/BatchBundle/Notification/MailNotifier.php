<?php

namespace Pim\Bundle\BatchBundle\Notification;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Pim\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Mailer job execution notifier
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailNotifier implements Notifier
{
    protected $logger;
    protected $securityContext;
    protected $twig;
    protected $mailer;

    public function __construct(
        BatchLogHandler $logger,
        SecurityContextInterface $securityContext,
        \Twig_Environment $twig,
        \Swift_Mailer $mailer
    ) {
        $this->logger          = $logger;
        $this->securityContext = $securityContext;
        $this->twig            = $twig;
        $this->mailer          = $mailer;
    }

    public function notify(JobExecution $jobExecution)
    {
        $user = $this->getUser();
        if (!$user) {
            return;
        }

        $filename = $this->logger->getFilename();
        $parameters = array(
            'user'         => $user,
            'jobExecution' => $jobExecution,
            'log'          => $this->logger->getRealPath($filename),
        );

        $txtBody  = $this->twig->render('PimBatchBundle:Mails:notification.txt.twig', $parameters);
        $htmlBody = $this->twig->render('PimBatchBundle:Mails:notification.html.twig', $parameters);

        $message = $this->mailer->createMessage();
        $message->setSubject('Job has been executed');
        $message->setFrom('no-reply@akeneo.com');
        $message->setTo($user->getEmail());
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');

        $this->mailer->send($message);
    }

    private function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }
}
