<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Test;

/**
 * Mail recorder for test purpose
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailRecorder implements \Swift_Events_SendListener
{
    /**
     * @var string The file in which will be stored the mails
     */
    private $filename;

    /**
     * @param string $filename location where to store the messages
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    /**
     * {@inheritdoc}
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
            return [];
        }

        $contents = unserialize(file_get_contents($this->filename));

        return is_array($contents) ? $contents : [];
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
        $messages = [];

        foreach ($this->findAll() as $message) {
            $emails = array_keys($message->getTo());
            if (in_array($email, $emails)) {
                $messages[] = $message;
            }
        }

        return $messages;
    }
}
