<?php

namespace Oro\Bundle\ImapBundle\Mail\Storage;

use Oro\Bundle\EmailBundle\Decoder\ContentDecoder;

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

    /**
     * @var string
     */
    private $contentTransferEncoding;

    /**
     * Constructor
     *
     * @param string $content
     * @param string $contentType
     * @param string $contentTransferEncoding
     * @param string $encoding
     */
    public function __construct($content, $contentType, $contentTransferEncoding, $encoding)
    {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->contentTransferEncoding = $contentTransferEncoding;
        $this->encoding = $encoding;
    }

    /**
     * Gets the content data
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Gets the decoded content data
     *
     * @param string $toEncoding The type of encoding that the content is being converted to.
     *                           Defaults to 'UTF-8'
     * @return string
     */
    public function getDecodedContent($toEncoding = 'UTF-8')
    {
        return ContentDecoder::decode(
            $this->content,
            $this->contentTransferEncoding,
            $this->encoding,
            $toEncoding
        );
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
     * Gets Content-Transfer-Encoding
     *
     * @return string
     */
    public function getContentTransferEncoding()
    {
        return $this->contentTransferEncoding;
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
