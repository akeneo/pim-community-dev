<?php

namespace Oro\Bundle\ImapBundle\Mail\Storage;

use Zend\Mail\Headers;
use \Zend\Mail\Storage\Part;
use \Zend\Mime\Decode;

class Attachment
{
    /**
     * @var Part
     */
    protected $part;

    /**
     * @param Part $part The message part contains the attachment
     */
    public function __construct(Part $part)
    {
        $this->part = $part;
    }

    /**
     * Gets the headers collection
     *
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->part->getHeaders();
    }

    /**
     * Gets a header in specified format
     *
     * @param  string $name The name of header, matches case-insensitive, but camel-case is replaced with dashes
     * @param  string $format change The type of return value to 'string' or 'array'
     * @return Headers
     */
    public function getHeader($name, $format = null)
    {
        return $this->part->getHeader($name, $format);
    }

    /**
     * Gets the attached file name
     *
     * @return Value
     */
    public function getFileName()
    {
        $value = '';
        $headers = $this->part->getHeaders();
        if ($headers->has('Content-Disposition')) {
            $contentDisposition = $this->part->getHeader('Content-Disposition');
            $value = Decode::splitContentType($contentDisposition->getFieldValue(), 'filename');
            $encoding = $contentDisposition->getEncoding();
        }
        if (empty($value) && $headers->has('Content-Type')) {
            /** @var \Zend\Mail\Header\ContentType $contentType */
            $contentType = $this->part->getHeader('Content-Type');
            $value = $contentType->getParameter('name');
            $encoding = $contentType->getEncoding();
        }
        if (empty($encoding)) {
            $encoding = 'ASCII';
        }

        return new Value($value, $encoding);
    }

    /**
     * Gets the attachment content
     *
     * @return Content
     */
    public function getContent()
    {
        if ($this->part->getHeaders()->has('Content-Type')) {
            /** @var \Zend\Mail\Header\ContentType $contentTypeHeader */
            $contentTypeHeader = $this->part->getHeader('Content-Type');
            $contentType = $contentTypeHeader->getType();
            $charset = $contentTypeHeader->getParameter('charset');
            $encoding = $charset !== null ? $charset : 'ASCII';
        } else {
            $contentType = 'text/plain';
            $encoding = 'ASCII';
        }

        if ($this->part->getHeaders()->has('Content-Transfer-Encoding')) {
            $contentTransferEncoding = $this->part->getHeader('Content-Transfer-Encoding')->getFieldValue();
        } else {
            $contentTransferEncoding = 'BINARY';
        }

        return new Content($this->part->getContent(), $contentType, $contentTransferEncoding, $encoding);
    }
}
