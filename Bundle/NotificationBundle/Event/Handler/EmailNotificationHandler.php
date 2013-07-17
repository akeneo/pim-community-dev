<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use JMS\JobQueueBundle\Entity\Job;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Symfony\Component\HttpFoundation\ParameterBag;

class EmailNotificationHandler implements EventHandlerInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    /**
     * Handle event
     *
     * @param NotificationEvent $event
     * @param EmailNotification[] $matchedNotifications
     * @return mixed
     */
    public function handle(NotificationEvent $event, $matchedNotifications)
    {
        foreach ($matchedNotifications as $notification) {
            $params = array(
                'event' => $event,
                'notification' => $notification,
            );

            $emailTemplate = $this->twig->loadTemplate($notification->getTemplate());
            $subject = ($emailTemplate->hasBlock("subject")
                ? $emailTemplate->renderBlock("subject", $params)
                : "oro_notification.default_notification_subject");
            $subject = trim($subject);

            $params = new ParameterBag(
                array(
                    'subject' => $subject,
                    'body'    => $emailTemplate->render($params),
                    'from'    => '',
                    'to'      => '',
                )
            );

            $this->notify($params);
            $this->addJob();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notify(ParameterBag $params)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($params->get('subject'))
            ->setFrom($params->get('from'))
            ->setTo($params->get('to'))
            ->setBody($params->get('body'));

        $this->mailer->send($message);
    }

    /**
     * Add swiftmailer spool send task to job queue if it has not been added earlier
     */
    protected function addJob()
    {
        $messageLimit = 100;
        $env          = 'prod';

        $command = 'swiftmailer:spool:send';
        $commandArgs = array(
            'message-limit' => $messageLimit,
            'env'           => $env,
        );

        if ($env == 'prod') {
            $commandArgs['no-debug'] = true;
        }

        $currJob = $this->em
            ->createQuery("SELECT j FROM JMSJobQueueBundle:Job j WHERE j.command = :command AND j.state <> :state")
            ->setParameter('command', $command)
            ->setParameter('state', Job::STATE_FINISHED)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$currJob) {
            $job = new Job($command, $commandArgs);
            $this->em->persist($job);
        }
    }
}
