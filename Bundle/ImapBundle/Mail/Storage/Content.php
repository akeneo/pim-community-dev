<?php

namespace Oro\Bundle\ImapBundle\Mail\Storage;

class Content
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $encoding;

    public function __construct($content, $contentType, $encoding)
    {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->encoding = $encoding;
    }

    /**
     * Gets the content data
     *
     * @return string|mixed
     * @throws \Zend\Mail\Storage\Exception\RuntimeException
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Gets the content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Gets the encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
