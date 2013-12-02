<?php

namespace Oro\Bundle\NotificationBundle\Processor;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationInterface;

class EmailNotificationProcessor extends AbstractNotificationProcessor
{
    const SEND_COMMAND = 'swiftmailer:spool:send';

    /** @var EmailRenderer */
    protected $renderer;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var string */
    protected $sendFrom;

    /** @var string */
    protected $messageLimit = 100;

    /** @var string */
    protected $env = 'prod';

    /**
     * Constructor
     *
     * @param EmailRenderer   $emailRenderer
     * @param \Swift_Mailer   $mailer
     * @param EntityManager   $em
     * @param string          $sendFrom
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailRenderer $emailRenderer,
        \Swift_Mailer $mailer,
        EntityManager $em,
        $sendFrom,
        LoggerInterface $logger
    ) {
        parent::__construct($logger, $em);

        $this->renderer = $emailRenderer;
        $this->mailer   = $mailer;
        $this->sendFrom = $sendFrom;
    }

    /**
     * Set message limit
     *
     * @param int $messageLimit
     */
    public function setMessageLimit($messageLimit)
    {
        $this->messageLimit = $messageLimit;
    }

    /**
     * Set environment
     *
     * @param string $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * Applies the given notifications to the given object
     *
     * @param mixed                        $object
     * @param EmailNotificationInterface[] $notifications
     * @param LoggerInterface              $logger Override for default logger. If this parameter is specified
     *                                             this logger will be used instead of a logger specified
     *                                             in the constructor
     */
    public function process($object, $notifications, LoggerInterface $logger = null)
    {
        if (!$logger) {
            $logger = $this->logger;
        }

        foreach ($notifications as $notification) {
            $emailTemplate = $notification->getTemplate();

            try {
                list ($subjectRendered, $templateRendered) = $this->renderer->compileMessage(
                    $emailTemplate,
                    array('entity' => $object)
                );
            } catch (\Twig_Error $ex) {
                $logger->error(
                    sprintf(
                        'Rendering of email template "%s"%s failed. %s',
                        $emailTemplate->getSubject(),
                        method_exists($emailTemplate, 'getId') ? sprintf(' (id: %d)', $emailTemplate->getId()) : '',
                        $ex->getMessage()
                    ),
                    array('exception' => $ex)
                );

                continue;
            }

            // TODO: use locale for subject and body
            $params = new ParameterBag(
                array(
                    'subject' => $subjectRendered,
                    'body'    => $templateRendered,
                    'from'    => $this->sendFrom,
                    'to'      => $notification->getRecipientEmails(),
                    'type'    => $emailTemplate->getType() == 'txt' ? 'text/plain' : 'text/html'
                )
            );

            $this->notify($params);
            $this->addJob(self::SEND_COMMAND);
        }
    }

    /**
     * Process with actual notification
     *
     * @param ParameterBag $params
     * @return bool
     */
    protected function notify(ParameterBag $params)
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
                ->setBody($params->get('body'), $params->get('type'));
            $this->mailer->send($message);
        }

        return true;
    }

    /**
     * Add swift mailer spool send task to job queue if it has not been added earlier
     *
     * @param string $command
     * @param array  $commandArgs
     * @param bool $needFlush
     * @return boolean|integer
     */
    protected function addJob($command, $commandArgs = array(), $needFlush = false)
    {
        $commandArgs = array_merge(
            array(
                '--message-limit=' . $this->messageLimit,
                '--env=' . $this->env,
                '--mailer=db_spool_mailer',
            ),
            $commandArgs
        );

        if ($this->env == 'prod') {
            $commandArgs[] = '--no-debug';
        }

        return parent::addJob($command, $commandArgs, $needFlush);
    }
}
