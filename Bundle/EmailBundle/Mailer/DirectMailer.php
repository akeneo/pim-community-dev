<?php

namespace Oro\Bundle\EmailBundle\Mailer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;

/**
 * The goal of this class is to send an email directly, not using a mail spool
 * even when it is configured for a base mailer
 */
class DirectMailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param \Swift_Mailer      $mailer
     * @param ContainerInterface $container
     */
    public function __construct(\Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->mailer    = $mailer;
        $this->container = $container;
    }

    /**
     * Creates a new instance of an email message.
     *
     * @param string $service
     * @return \Swift_Mime_Message
     */
    public function createMessage($service = 'message')
    {
        return $this->mailer->createMessage($service);
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
        $result    = 0;
        $transport = $this->mailer->getTransport();
        if ($transport instanceof \Swift_Transport_SpoolTransport) {
            $realTransport = $this->findRealTransport();
            if ($realTransport) {
                // start a transport if needed
                $needToStopRealTransport = false;
                if (!$realTransport->isStarted()) {
                    $realTransport->start();
                    $needToStopRealTransport = true;
                }
                // send a mail
                $sendException = null;
                try {
                    $result = $realTransport->send($message, $failedRecipients);
                } catch (\Swift_RfcComplianceException $ex) {
                    foreach ($message->getTo() as $address => $name) {
                        $failedRecipients[] = $address;
                    }
                } catch (\Exception $unexpectedEx) {
                    $sendException = $unexpectedEx;
                }
                // stop a transport if it was started before
                if ($needToStopRealTransport) {
                    try {
                        $realTransport->stop();
                    } catch (\Exception $ex) {
                        // ignore errors here
                    }
                }
                // rethrow send failure
                if ($sendException) {
                    throw $sendException;
                }
            }
        } else {
            $result = $this->mailer->send($message, $failedRecipients);
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
            if ($mailer === $this->mailer) {
                $realTransport = $this->container->get(sprintf('swiftmailer.mailer.%s.transport.real', $name));
                break;
            }
        }

        return $realTransport;
    }
}
