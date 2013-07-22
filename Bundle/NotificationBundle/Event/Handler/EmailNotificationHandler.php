<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\JobQueueBundle\Entity\Job;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Symfony\Component\HttpFoundation\ParameterBag;

class EmailNotificationHandler implements EventHandlerInterface
{
    const SEND_COMMAND = 'oro:spool:send';

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $sendFrom;

    public function __construct(\Twig_Environment $twig, \Swift_Mailer $mailer, ObjectManager $em, $sendFrom)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->sendFrom = $sendFrom;
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
                'entity' => $event->getEntity(),
                'templateName' => $notification->getTemplate(),
            );

            $template = str_replace('Bundle:', '/../emails/', $notification->getTemplate());
            $emailTemplate = $this->twig->loadTemplate($template);
            // TODO: There's a bug with sandbox and forms, to be investigated
            //$emailTemplate = $this->twig->loadTemplate('@OroNotification\email_sandbox.html.twig');
            $subject = ($emailTemplate->hasBlock("subject")
                ? $emailTemplate->renderBlock("subject", $params)
                : "oro_notification.default_notification_subject");
            $subject = trim($subject);

            $recipientEmails = $this->em->getRepository('Oro\Bundle\NotificationBundle\Entity\RecipientList')
                ->getRecipientEmails($notification->getRecipientList());

            $params = new ParameterBag(
                array(
                    'subject' => $subject,
                    'body'    => $emailTemplate->render($params),
                    'from'    => $this->sendFrom,
                    'to'      => $recipientEmails,
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

        $command = self::SEND_COMMAND;
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
            $this->em->flush($job);
        }
    }
}
