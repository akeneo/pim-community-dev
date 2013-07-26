<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
     * @var string
     */
    protected $sendFrom;

    /**
     * @var string
     */
    protected $messageLimit = 100;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $env = 'prod';

    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        ObjectManager $em,
        $sendFrom,
        Logger $logger,
        SecurityContextInterface $securityContext
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->sendFrom = $sendFrom;
        $this->logger = $logger;

        $this->user = $securityContext->getToken() ? $securityContext->getToken()->getUser() : false;
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
            $emailTemplate = $notification->getTemplate();
            $templateParams = array(
                'event'        => $event,
                'notification' => $notification,
                'entity'       => $entity,
                'templateName' => $emailTemplate,
                'user'         => $this->user,
            );

            $recipientEmails = $this->em->getRepository('Oro\Bundle\NotificationBundle\Entity\RecipientList')
                ->getRecipientEmails($notification->getRecipientList(), $entity);

            $content = $emailTemplate->getContent();
            // ensure we have no html tags in txt template
            $content = $emailTemplate->getType() == 'txt' ? strip_tags($content) : $content;

            try {
                $templateRendered = $this->twig->render($content, $templateParams);
                $subjectRendered = $this->twig->render($emailTemplate->getSubject(), $templateParams);
            } catch (\Twig_Error $e) {
                $templateRendered = false;

                $this->logger->log(
                    Logger::ERROR,
                    sprintf(
                        'Error rendering email template (id: %d), %s',
                        $emailTemplate->getId(),
                        $e->getMessage()
                    )
                );
            }

            if ($templateRendered === false) {
                break;
            }

            // TODO: use locale for subject and body
            $params = new ParameterBag(
                array(
                    'subject' => $subjectRendered,
                    'body'    => $templateRendered,
                    'from'    => $this->sendFrom,
                    'to'      => $recipientEmails,
                )
            );

            $this->notify($params);
            $this->addJob(self::SEND_COMMAND);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notify(ParameterBag $params)
    {
        $recipients = $params->get('to');
        if (empty($recipients)) {
            return false;
        }

        foreach ($recipients as $email) {
            $message = \Swift_Message::newInstance()
                ->setSubject($params->get('subject'))
                ->setFrom($params->get('from'))
                ->setTo($email)
                ->setBody($params->get('body'));
            $this->mailer->send($message);
        }

        return true;
    }

    /**
     * Add swiftmailer spool send task to job queue if it has not been added earlier
     */
    public function addJob($command, $commandArgs = array())
    {
        $commandArgs = array_merge(
            array(
                'message-limit' => $this->messageLimit,
                'env'           => $this->env,
            ),
            $commandArgs
        );

        if ($commandArgs['env'] == 'prod') {
            $commandArgs['no-debug'] = true;
        }

        return parent::addJob($command, $commandArgs);
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
