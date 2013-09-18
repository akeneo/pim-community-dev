<?php

namespace Oro\Bundle\ImapBundle\Entity;

interface ImapConfigurationOwnerInterface
{
    /**
     * Getter for imap configuration
     *
     * @return ImapEmailOrigin
     */
    public function getImapConfiguration();

    /**
     * Setter for imap configuration
     *
     * @param ImapEmailOrigin $imapConfiguration
     *
     * @return $this
     */
    public function setImapConfiguration(ImapEmailOrigin $imapConfiguration);
}
