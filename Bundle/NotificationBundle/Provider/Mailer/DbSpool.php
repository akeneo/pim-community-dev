<?php

namespace Oro\Bundle\NotificationBundle\Provider\Mailer;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\NotificationBundle\Entity\SpoolItem;

class DbSpool extends \Swift_ConfigurableSpool
{
    const STATUS_FAILED     = 0;
    const STATUS_READY      = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETE   = 3;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var bool
     */
    protected $flushOnQueue = false;

    public function __construct(EntityManager $em, $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    /**
     * Starts this Spool mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Spool mechanism.
     */
    public function stop()
    {
    }

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Queues a message.
     *
     * @param \Swift_Mime_Message $message The message to store
     * @return boolean Whether the operation has succeeded
     * @throws \Swift_IoException if the persist fails
     */
    public function queueMessage(\Swift_Mime_Message $message)
    {
        /** @var SpoolItem $mailObject */
        $mailObject = new $this->entityClass;
        $mailObject->setMessage(serialize($message));
        $mailObject->setStatus(self::STATUS_READY);

        try {
            $this->em->persist($mailObject);
            if ($this->flushOnQueue) {
                $this->em->flush($mailObject);
            }
        } catch (\Exception $e) {
            throw new \Swift_IoException("Unable to persist object for enqueuing message");
        }

        return true;
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param \Swift_Transport $transport         A transport instance
     * @param string[]        &$failedRecipients  An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        if (!$transport->isStarted()) {
            $transport->start();
        }

        $repo = $this->em->getRepository($this->entityClass);
        $limit = $this->getMessageLimit();
        $limit = $limit > 0 ? $limit : null;
        $emails = $repo->findBy(array("status" => self::STATUS_READY), null, $limit);
        if (!count($emails)) {
            return 0;
        }

        $failedRecipients = (array) $failedRecipients;
        $count = 0;
        $time = time();
        /** @var SpoolItem $email */
        foreach ($emails as $email) {
            $email->setStatus(self::STATUS_PROCESSING);
            $this->em->persist($email);
            $this->em->flush();

            /** @var \Swift_Message $message */
            $message = unserialize($email->getMessage());
            $count += $transport->send($message, $failedRecipients);
            $this->em->remove($email);
            $this->em->flush();

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }

    /**
     * @param bool $flush
     */
    public function setFlushOnQueue($flush)
    {
        $this->flushOnQueue = $flush;
    }
}
