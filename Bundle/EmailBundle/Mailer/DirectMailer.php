<?php

namespace Oro\Bundle\EmailBundle\Mailer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Oro\Bundle\EmailBundle\Exception\NotSupportedException;

/**
 * The goal of this class is to send an email directly, not using a mail spool
 * even when it is configured for a base mailer
 */
class DirectMailer extends \Swift_Mailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $baseMailer;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param \Swift_Mailer      $baseMailer
     * @param ContainerInterface $container
     */
    public function __construct(\Swift_Mailer $baseMailer, ContainerInterface $container)
    {
        $this->baseMailer = $baseMailer;
        $this->container  = $container;

        $transport = $this->baseMailer->getTransport();
        if ($transport instanceof \Swift_Transport_SpoolTransport) {
            $transport = $this->findRealTransport();
            if (!$transport) {
                $transport = \Swift_NullTransport::newInstance();
            }
        }
        parent::__construct($transport);
    }

    /**
     * Register a plugin using a known unique key (e.g. myPlugin).
     *
     * @param \Swift_Events_EventListener $plugin
     * @throws \Oro\Bundle\EmailBundle\Exception\NotSupportedException
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        throw new NotSupportedException('The registerPlugin() is not supported for this mailer.');
    }

    /**
     * Sends the given message.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * @param \Swift_Mime_Message $message
     * @param array               $failedRecipients An array of failures by-reference
     *
     * @return int The number of recipients who were accepted for delivery
     * @throws \Exception
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $result = 0;
        // start a transport if needed
        $needToStopRealTransport = false;
        if (!$this->getTransport()->isStarted()) {
            $this->getTransport()->start();
            $needToStopRealTransport = true;
        }
        // send a mail
        $sendException = null;
        try {
            $result = parent::send($message, $failedRecipients);
        } catch (\Exception $unexpectedEx) {
            $sendException = $unexpectedEx;
        }
        // stop a transport if it was started before
        if ($needToStopRealTransport) {
            try {
                $this->getTransport()->stop();
            } catch (\Exception $ex) {
                // ignore errors here
            }
        }
        // rethrow send failure
        if ($sendException) {
            throw $sendException;
        }

        return $result;
    }

    /**
     * Returns a real transport used to send mails by a mailer specified in the constructor of this class
     *
     * @return \Swift_Transport|null
     */
    protected function findRealTransport()
    {
        $realTransport = null;
        $mailers       = array_keys($this->container->getParameter('swiftmailer.mailers'));
        foreach ($mailers as $name) {
            if ($this->container instanceof IntrospectableContainerInterface
                && !$this->container->initialized(sprintf('swiftmailer.mailer.%s', $name))
            ) {
                continue;
            }
            $mailer = $this->container->get(sprintf('swiftmailer.mailer.%s', $name));
            if ($mailer === $this->baseMailer) {
                $realTransport = $this->container->get(sprintf('swiftmailer.mailer.%s.transport.real', $name));
                break;
            }
        }

        return $realTransport;
    }
}
