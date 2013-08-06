<?php

namespace Oro\Bundle\EmailBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;

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
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param EmailOwnerProviderStorage $emailOwnerProviderStorage
     * @param EntityManager $em
     */
    public function __construct(EmailOwnerProviderStorage $emailOwnerProviderStorage, EntityManager $em)
    {
        $this->emailOwnerProviderStorage = $emailOwnerProviderStorage;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner($email)
    {
        $emailOwner = null;
        foreach ($this->emailOwnerProviderStorage->getProviders() as $provider) {
            $emailOwner = $provider->findEmailOwner($this->em, $email);
            if ($emailOwner !== null) {
                break;
            }
        }

        return $emailOwner;
    }
}
