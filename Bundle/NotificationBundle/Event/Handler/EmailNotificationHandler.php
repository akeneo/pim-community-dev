<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Oro\Bundle\NotificationBundle\DependencyInjection\Compiler\TemplatesCompilerPass;

class EmailNotificationHandler extends EventHandlerAbstract
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

    /**
     * @var string
     */
    protected $messageLimit = 100;

    /**
     * @var string
     */
    protected $env = 'prod';

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
        $entity = $event->getEntity();

        foreach ($matchedNotifications as $notification) {
            $templateParams = array(
                'event' => $event,
                'notification' => $notification,
                'entity' => $entity,
                'templateName' => $notification->getTemplate(),
            );

            $template = str_replace(
                'Bundle:',
                '/../' . TemplatesCompilerPass::DIR_NAME . DIRECTORY_SEPARATOR,
                $notification->getTemplate()
            );

            $emailTemplate = $this->twig->loadTemplate($template);
            // TODO: There's a bug with sandbox and forms, to be investigated
            //$emailTemplate = $this->twig->loadTemplate('@OroNotification\email_sandbox.html.twig');

            $subject = ($emailTemplate->hasBlock("subject")
                ? trim($emailTemplate->renderBlock("subject", $templateParams))
                : "oro_notification.default_notification_subject");

            $recipientEmails = $this->em->getRepository('Oro\Bundle\NotificationBundle\Entity\RecipientList')
                ->getRecipientEmails($notification->getRecipientList(), $entity);

            $params = new ParameterBag(
                array(
                    'subject' => $subject,
                    'body'    => $emailTemplate->render($templateParams),
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
    public function addJob()
    {
        $commandArgs = array(
            'message-limit' => $this->messageLimit,
            'env'           => $this->env,
        );

        if ($commandArgs['env'] == 'prod') {
            $commandArgs['no-debug'] = true;
        }

        return parent::addJob(self::SEND_COMMAND, $commandArgs);
    }

    /**
     * @param $messageLimit
     */
    public function setMessageLimit($messageLimit)
    {
        $this->messageLimit = $messageLimit;
    }

    /**
     * @param string $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }
}
