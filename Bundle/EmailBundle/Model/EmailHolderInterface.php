<?php

namespace Oro\Bundle\EmailBundle\Model;

/**
 * Represents an subject which may receive email messages
 */
interface EmailHolderInterface
{
    /**
     * Gets an email address which can be used to send messages
     *
     * @return string
     */
    public function getEmail();
}
