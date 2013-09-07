<?php

namespace Oro\Bundle\ImapBundle\Manager\DTO;

class EmailBody
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var bool
     */
    protected $bodyIsText;

    /**
     * Get body content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set body content.
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Indicate whether email body is a text or html.
     *
     * @return bool true if body is text/plain; otherwise, the body content is text/html
     */
    public function getBodyIsText()
    {
        return $this->bodyIsText;
    }

    /**
     * Set body content type.
     *
     * @param bool $bodyIsText true for text/plain, false for text/html
     * @return $this
     */
    public function setBodyIsText($bodyIsText)
    {
        $this->bodyIsText = $bodyIsText;

        return $this;
    }
}
