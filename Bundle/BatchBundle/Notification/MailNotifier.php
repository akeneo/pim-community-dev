<?php

namespace Oro\Bundle\BatchBundle\Notification;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Oro\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Notify Job execution result by mail
 *
 */
class MailNotifier implements Notifier
{
    /**
     * @var BatchLogHandler $logger
     */
    protected $logger;

    /**
     * @var SecurityContextInterface $securityContext
     */
    protected $securityContext;

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
     * @param BatchLogHandler          $logger
     * @param SecurityContextInterface $securityContext
     * @param \Twig_Environment        $twig
     * @param \Swift_Mailer            $mailer
     * @param string                   $senderEmail
     */
    public function __construct(
        BatchLogHandler $logger,
        SecurityContextInterface $securityContext,
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        $senderEmail
    ) {
        $this->logger          = $logger;
        $this->securityContext = $securityContext;
        $this->twig            = $twig;
        $this->mailer          = $mailer;
        $this->senderEmail     = $senderEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(JobExecution $jobExecution)
    {
        $user = $this->getUser();
        if (!$user) {
            return;
        }

        $parameters = array(
            'user'         => $user,
            'jobExecution' => $jobExecution,
            'log'          => $this->logger->getFilename(),
        );

        $txtBody  = $this->twig->render('OroBatchBundle:Mails:notification.txt.twig', $parameters);
        $htmlBody = $this->twig->render('OroBatchBundle:Mails:notification.html.twig', $parameters);

        $message = $this->mailer->createMessage();
        $message->setSubject('Job has been executed');
        $message->setFrom($this->senderEmail);
        $message->setTo($user->getEmail());
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Get the current authenticated user
     *
     * @return null|UserInterface
     */
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
