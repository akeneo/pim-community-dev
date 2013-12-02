<?php

namespace Oro\Bundle\EmailBundle\Model;

/**
 * Represents an email message template
 */
interface EmailTemplateInterface
{
    /**
     * Gets email template type
     *
     * @return string
     */
    public function getType();

    /**
     * Gets email subject
     *
     * @return string
     */
    public function getSubject();

    /**
     * Gets email template content
     *
     * @return string
     */
    public function getContent();
}
