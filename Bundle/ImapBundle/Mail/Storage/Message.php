<?php

namespace Oro\Bundle\ImapBundle\Mail\Storage;

use \Zend\Mail\Header\HeaderInterface;
use \Zend\Mail\Storage\Part;
use \Zend\Mime\Decode;

class Message extends \Zend\Mail\Storage\Message
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
    }

    /**
     * Gets the message attachments
     *
     * @return Body
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function getBody()
    {
        if (!$this->isMultipart()) {
            return new Body($this);
        }

        foreach ($this as $part) {
            $contentType = $this->getPartContentType($part);
            if ($contentType !== null) {
                if ($contentType->getParameter('name') === null) {
                    return new Body($part);
                }
            }
        }

        throw new \Zend\Mail\Storage\Exception\RuntimeException('Cannot find a message body.');
    }

    /**
     * Gets the message attachments
     *
     * @return Attachment[]
     */
    public function getAttachments()
    {
        if (!$this->isMultipart()) {
            return array();
        }

        $result = array();
        foreach ($this as $part) {
            /** @var Part $part */
            $contentType = $this->getPartContentType($part);
            if ($contentType !== null) {
                $name = $contentType->getParameter('name');
                if ($name !== null) {
                    $contentDisposition = $this->getPartContentDisposition($part);
                    if ($contentDisposition !== null) {
                        if (null !== Decode::splitContentType('attachment')) {
                            $result[] = new Attachment($part);
                        }
                    } else {
                        // The Content-Disposition may be missed, because it is introduced only in RFC 2183
                        // In this case it is assumed that any part which has ";name="
                        // in the Content-Type is an attachment
                        $result[] = new Attachment($part);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Gets the Content-Type for the given part
     *
     * @param Part $part The message part
     * @return \Zend\Mail\Header\ContentType|null
     */
    protected function getPartContentType($part)
    {
        return $part->getHeaders()->has('Content-Type')
            ? $part->getHeader('Content-Type')
            : null;
    }

    /**
     * Gets the Content-Disposition for the given part
     *
     * @param Part $part The message part
     * @param bool $format Can be FORMAT_RAW or FORMAT_ENCODED, see HeaderInterface::FORMAT_* constants
     * @return string|null
     */
    protected function getPartContentDisposition($part, $format = HeaderInterface::FORMAT_RAW)
    {
        return $part->getHeaders()->has('Content-Disposition')
            ? $part->getHeader('Content-Disposition')->getFieldValue($format)
            : null;
    }
}
