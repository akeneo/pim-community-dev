<?php

namespace Oro\Bundle\EmailBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;

/**
 * Defines an interface of an email owner provider
 */
interface EmailOwnerProviderInterface
{
    /**
     * Get full name of email owner class
     *
     * @return string
     */
    public function getEmailOwnerClass();

    /**
     * Find an entity object which is an owner of the given email address
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param string $email
     * @return EmailOwnerInterface
     */
    public function findEmailOwner(EntityManager $em, $email);
}
