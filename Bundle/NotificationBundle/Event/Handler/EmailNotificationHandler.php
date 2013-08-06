<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use Monolog\Logger;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\NotificationBundle\Event\NotificationEvent;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class EmailNotificationHandler extends EventHandlerAbstract
{
    const SEND_COMMAND       = 'oro:spool:send';

    /** @var \Twig_Environment */
    protected $twig;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var string */
    protected $sendFrom;

    /** @var string */
    protected $messageLimit = 100;

    /** @var Logger */
    protected $logger;

    /** @var string */
    protected $env = 'prod';

    /** @var  Cache|null */
    protected $cache;

    /** @var  ConfigProvider */
    protected $configProvider;

    /** @var  string */
    protected $cacheKey;

    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        ObjectManager $em,
        $sendFrom,
        Logger $logger,
        SecurityContextInterface $securityContext,
        ConfigProvider $configProvider,
        Cache $cache,
        $cacheKey
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->sendFrom = $sendFrom;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->cache = $cache;

        $this->cacheKey = $cacheKey;
        $this->user = $this->getUser($securityContext);
        $this->configureSandbox($this->twig);
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
                $subjectRendered = false;

                $this->logger->log(
                    Logger::ERROR,
                    sprintf(
                        'Error rendering email template (id: %d), %s',
                        $emailTemplate->getId(),
                        $e->getMessage()
                    )
                );
            }

            if ($templateRendered === false || $subjectRendered === false) {
                break;
            }

            // TODO: use locale for subject and body
            $params = new ParameterBag(
                array(
                    'subject' => $subjectRendered,
                    'body'    => $templateRendered,
                    'from'    => $this->sendFrom,
                    'to'      => $recipientEmails,
                    'type'    => $emailTemplate->getType() == 'txt' ? 'text/plain' : 'text/html'
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
                ->setBody($params->get('body'), $params->get('type'));
            $this->mailer->send($message);
        }

        return true;
    }

    /**
     * Add swiftmailer spool send task to job queue if it has not been added earlier
     *
     * @param string $command
     * @param array $commandArgs
     * @return boolean|integer
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
     * Configure sandbox form config data
     *
     * @param \Twig_Environment $twig
     */
    protected function configureSandbox(\Twig_Environment $twig)
    {
        $allowedData = $this->cache->fetch($this->cacheKey);

        if (false === $allowedData) {
            $allowedData = $this->prepareConfiguration();
            $this->cache->save($this->cacheKey, serialize($allowedData));
        } else {
            $allowedData = unserialize($allowedData);
        }
        /** @var \Twig_Extension_Sandbox $sandbox */
        $sandbox = $twig->getExtension('sandbox');
        /** @var \Twig_Sandbox_SecurityPolicy $security */
        $security = $sandbox->getSecurityPolicy();
        $security->setAllowedMethods($allowedData);
    }

    /**
     * Prepare configuration from entity config
     *
     * @return array
     */
    private function prepareConfiguration()
    {
        $configuration = array();

        /**
         * @TODO Change when new code of entity config will be merged
         */
        foreach ($this->configProvider->getAllConfigurableEntityNames() as $className) {
            $config = $this->configProvider->getConfig($className);
            $fields = $config->getFields(
                function (FieldConfig $field) {
                    return $field->is('available_in_template');
                }
            );

            if (!$fields->isEmpty()) {
                $configuration[$className] = array();
                foreach ($fields as $field) {
                    $configuration[$className][] = 'get' . strtolower($field->getCode());
                }
            }
        }

        return $configuration;
    }

    /**
     * Return current user
     *
     * @param  SecurityContextInterface $securityContext
     * @return User|bool
     */
    private function getUser(SecurityContextInterface $securityContext)
    {
        return $securityContext->getToken() && !is_string($securityContext->getToken()->getUser())
            ? $securityContext->getToken()->getUser() : false;
    }
}
