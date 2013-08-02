<?php

namespace Oro\Bundle\EmailBundle\Entity\Provider;

use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;

/**
 * Defines an interface of an email owner provider
 */
interface EmailOwnerProviderInterface
{
    /**
     * Find an entity object which is an owner of the given email address
     *
     * @param string $emailAddress
     * @return EmailOwnerInterface
     */
    public function findEmailOwner($emailAddress);
}
