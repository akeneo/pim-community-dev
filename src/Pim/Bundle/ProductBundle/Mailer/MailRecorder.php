<?php

namespace Pim\Bundle\ProductBundle\Mailer;

/**
 * Mail recorder for test purpose
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailRecorder implements \Swift_Events_SendListener
{
    private $filename;

    /**
     * @param string location where to store the messages
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        $contents = $this->findAll();
        $contents[] = $evt->getMessage();

        file_put_contents($this->filename, serialize($contents));
    }

    /**
     * Return all recorded mails
     *
     * @return array
     */
    public function findAll()
    {
        if (!file_exists($this->filename)) {
            return array();
        }

        $contents = unserialize(file_get_contents($this->filename));

        return is_array($contents) ? $contents : array();
    }

    /**
     * Clear the stored mails
     */
    public function clear()
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    /**
     * Get the mails sent to a specific email
     *
     * @param string $email
     *
     * @return array
     */
    public function getMailsSentTo($email)
    {
        $messages = array();

        foreach ($this->findAll() as $message) {
            $emails = array_keys($message->getTo());
            if (in_array($email, $emails)) {
                $messages[] = $message;
            }
        }

        return $messages;
    }
}
