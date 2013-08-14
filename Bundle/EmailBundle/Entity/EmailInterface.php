<?php

namespace Oro\Bundle\EmailBundle\Entity;

interface EmailInterface
{
    /**
     * Get name of field contains an email address
     *
     * @return string
     */
    public function getEmailField();

    /**
     * Get entity unique id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get email owner entity
     *
     * @return EmailOwnerInterface
     */
    public function getEmailOwner();
}
