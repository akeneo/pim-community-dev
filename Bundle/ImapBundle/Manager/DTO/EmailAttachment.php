<?php

namespace Oro\Bundle\ImapBundle\Manager\DTO;

class EmailAttachment
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var string
     */
    protected $contentTransferEncoding;

    /**
     * @var string
     */
    protected $content;

    /**
     * Get attachment file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set attachment file name
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get content type. It may be any MIME type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set content type
     *
     * @param string $contentType any MIME type
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get encoding type of attachment content
     *
     * @return string
     */
    public function getContentTransferEncoding()
    {
        return $this->contentTransferEncoding;
    }

    /**
     * Set encoding type of attachment content
     *
     * @param string $contentTransferEncoding
     * @return $this
     */
    public function setContentTransferEncoding($contentTransferEncoding)
    {
        $this->contentTransferEncoding = $contentTransferEncoding;

        return $this;
    }

    /**
     * Get content of email attachment
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content of email attachment
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
