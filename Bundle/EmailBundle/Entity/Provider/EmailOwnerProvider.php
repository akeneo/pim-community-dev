<?php

namespace Oro\Bundle\EmailBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;

/**
 * Email owner provider chain
 */
class EmailOwnerProvider
{
    /**
     * @var EmailOwnerProviderStorage
     */
    private $emailOwnerProviderStorage;

    /**
     * Constructor
     *
     * @param EmailOwnerProviderStorage $emailOwnerProviderStorage
     */
    public function __construct(EmailOwnerProviderStorage $emailOwnerProviderStorage)
    {
        $this->emailOwnerProviderStorage = $emailOwnerProviderStorage;
    }

    /**
     * Find an entity object which is an owner of the given email address
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param string $email
     * @return EmailOwnerInterface
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        $emailOwner = null;
        foreach ($this->emailOwnerProviderStorage->getProviders() as $provider) {
            $emailOwner = $provider->findEmailOwner($em, $email);
            if ($emailOwner !== null) {
                break;
            }
        }

        return $emailOwner;
    }
}
