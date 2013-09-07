<?php

namespace Oro\Bundle\ImapBundle\Manager;

use Oro\Bundle\ImapBundle\Connector\ImapConnector;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Manager\DTO\ItemId;
use Oro\Bundle\ImapBundle\Manager\DTO\Email;
use Zend\Mail\Headers;
use Zend\Mail\Header\HeaderInterface;
use Zend\Mail\Header\AbstractAddressList;
use Zend\Mail\Address\AddressInterface;

class ImapEmailManager
{
    /**
     * @var ImapConnector
     */
    protected $connector;

    /**
     * A mailbox name all email related actions are performed for
     *
     * @var string
     */
    protected $selectedFolder = 'inbox';

    /**
     * Constructor
     *
     * @param ImapConnector $connector
     */
    public function __construct(ImapConnector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Get selected folder
     *
     * @return string
     */
    public function getSelectedFolder()
    {
        return $this->selectedFolder;
    }

    /**
     * Set selected folder
     *
     * @param $folder
     */
    public function selectFolder($folder)
    {
        $this->selectedFolder = $folder;
    }

    /**
     * Retrieve emails by the given criteria
     *
     * @param SearchQuery $query
     * @return Email[]
     */
    public function getEmails(SearchQuery $query = null)
    {
        $response = $this->connector->findItems(
            $this->getSelectedFolder(),
            $query
        );

        $result = array();
        foreach ($response as $msg) {
            $headers = $msg->getHeaders();
            $email = new Email($msg);
            $email
                ->setId(
                    new ItemId(
                        intval($headers->get('UID')->getFieldValue()),
                        $this->connector->getUidValidity()
                    )
                )
                ->setSubject($this->getString($headers, 'Subject'))
                ->setFrom($this->getString($headers, 'From'))
                ->setSentAt($this->getDateTime($headers, 'Date'))
                ->setReceivedAt($this->getReceivedAt($headers))
                ->setInternalDate($this->getDateTime($headers, 'InternalDate'))
                ->setImportance($this->getImportance($headers))
                ->setMessageId($this->getString($headers, 'Message-ID'))
                ->setXMessageId($this->getString($headers, 'X-GM-MSG-ID'))
                ->setXThreadId($this->getString($headers, 'X-GM-THR-ID'));
            foreach ($this->getRecipients($headers, 'To') as $val) {
                $email->addToRecipient($val);
            }
            foreach ($this->getRecipients($headers, 'Cc') as $val) {
                $email->addCcRecipient($val);
            }
            foreach ($this->getRecipients($headers, 'Bcc') as $val) {
                $email->addBccRecipient($val);
            }

            $result[] = $email;
        }

        return $result;
    }

    /**
     * Gets a string representation of an email header
     *
     * @param Headers $headers
     * @param string $name
     * @return string
     */
    protected function getString(Headers $headers, $name)
    {
        $header = $headers->get($name);
        if ($header === false) {
            return '';
        }

        return $header->getFieldValue();
    }

    /**
     * Gets an email header as DateTime type
     *
     * @param Headers $headers
     * @param string $name
     * @return \DateTime
     */
    protected function getDateTime(Headers $headers, $name)
    {
        $val = $headers->get($name);
        if ($val instanceof HeaderInterface) {
            $dt = new \DateTime($val->getFieldValue());
            $dt->setTimezone(new \DateTimeZone('UTC'));

            return $dt;
        }

        return new \DateTime('0001-01-01', new \DateTimeZone('UTC'));
    }

    /**
     * Gets DateTime when an email is received
     *
     * @param Headers $headers
     * @return \DateTime
     */
    protected function getReceivedAt(Headers $headers)
    {
        $val = $headers->get('Received');
        $str = '';
        if ($val instanceof HeaderInterface) {
            $str = $val->getFieldValue();
        } elseif ($val instanceof \ArrayIterator) {
            $val->rewind();
            $str = $val->current()->getFieldValue();
        }

        $delim = strrpos($str, ';');
        if ($delim !== false) {
            $str = trim(preg_replace('@[\r\n]+@', '', substr($str, $delim + 1)));
            $dt = new \DateTime($str);
            $dt->setTimezone(new \DateTimeZone('UTC'));

            return $dt;
        }

        return new \DateTime('0001-01-01', new \DateTimeZone('UTC'));
    }

    /**
     * Get an email recipients
     *
     * @param Headers $headers
     * @param string $name
     * @return string[]
     */
    protected function getRecipients(Headers $headers, $name)
    {
        $result = array();
        $val = $headers->get($name);
        if ($val instanceof AbstractAddressList) {
            /** @var AddressInterface $addr */
            foreach ($val->getAddressList() as $addr) {
                $result[] = $addr->toString();
            }
        }
        return $result;
    }

    /**
     * Gets an email importance
     *
     * @param Headers $headers
     * @return integer
     */
    protected function getImportance(Headers $headers)
    {
        $importance = $headers->get('Importance');
        if ($importance instanceof HeaderInterface) {
            switch (strtolower($importance->getFieldValue())) {
                case 'high':
                    return 1;
                case 'low':
                    return -1;
                default:
                    return 0;
            }
        }

        $labels = $headers->get('X-GM-LABELS');
        if ($labels instanceof HeaderInterface) {
            if ($labels->getFieldValue() === '\\\\Important') {
                return 1;
            }
        } elseif ($labels instanceof \ArrayIterator) {
            foreach ($labels as $label) {
                if ($label instanceof HeaderInterface && $label->getFieldValue() === '\\\\Important') {
                    return 1;
                }
            }
        }

        return 0;
    }
}
