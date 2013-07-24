<?php

namespace Oro\Bundle\ImapBundle\Extensions\Zend\Mail\Storage;

use Zend\Mail\Headers;
use \Zend\Mail\Storage\Part;
use \Zend\Mime\Decode;

class Body
{
    const FORMAT_TEXT = false;
    const FORMAT_HTML = true;

    /**
     * @var Part
     */
    protected $part;

    /**
     * @param Part $part The message part contains the message body
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
     * Gets a string contains the message body content
     *
     * @param bool $format The required format of the body. Can be FORMAT_TEXT or FORMAT_HTML
     * @return Content
     * @throws Exception\InvalidBodyFormatException
     */
    public function getContent($format = Body::FORMAT_TEXT)
    {
        if (!$this->part->isMultipart()) {
            if ($format === Body::FORMAT_TEXT) {
                return $this->extractContent($this->part);
            }
        } else {
            $i = 0;
            foreach ($this->part as $part) {
                $contentTypeHeader = $this->getPartContentType($part);
                if ($contentTypeHeader !== null) {
                    if ($format === Body::FORMAT_TEXT && $contentTypeHeader->getType() === 'text/plain') {
                        return $this->extractContent($part);
                    } elseif ($format === Body::FORMAT_HTML && $contentTypeHeader->getType() === 'text/html') {
                        return $this->extractContent($part);
                    }
                }
            }
        }

        throw new Exception\InvalidBodyFormatException(sprintf(
            'A messages does not have %s content.',
            $format === Body::FORMAT_TEXT ? 'TEXT' : 'HTML'
        ));
    }

    /**
     * Extracts body content from the given part
     *
     * @param Part $part The message part where the content is stored
     * @return Content
     */
    protected function extractContent($part)
    {
        /** @var \Zend\Mail\Header\ContentType $contentTypeHeader */
        $contentTypeHeader = $this->getPartContentType($part);
        if ($contentTypeHeader !== null) {
            $contentType = $contentTypeHeader->getType();
            $charset = $contentTypeHeader->getParameter('charset');
            $encoding = $charset !== null ? $charset : 'ASCII';
        } else {
            $contentType = 'text/plain';
            $encoding = 'ASCII';
        }

        if ($part->getHeaders()->has('Content-Transfer-Encoding')) {
            $contentTransferEncoding = $part->getHeader('Content-Transfer-Encoding')->getFieldValue();
            switch (strtolower($contentTransferEncoding)) {
                case 'base64':
                    $content = base64_decode($part->getContent());
                    break;
                case 'quoted-printable':
                    $content = quoted_printable_decode($part->getContent());
                    break;
                default:
                    $content = $part->getContent();
                    break;
            }
        } else {
            $content = $part->getContent();
        }

        return new Content($content, $contentType, $encoding);
    }

    /**
     * Gets the Content-Type for the given part
     *
     * @param Part $part The message part
     * @return \Zend\Mail\Header\ContentType|null
     */
    protected function getPartContentType($part)
    {
        if (!$part->getHeaders()->has('Content-Type')) {
            return null;
        }

        return $part->getHeader('Content-Type');
    }
}
