<?php

namespace Oro\Bundle\EmailBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;

/**
 * Email owner provider chain
 */
class EmailOwnerProvider
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @var EmailOwnerProviderInterface[]
     */
    private $emailOwnerProviders = array();

    /**
     * Add email owner provider
     *
     * @param EmailOwnerProviderInterface $provider
     */
    public function addProvider(EmailOwnerProviderInterface $provider)
    {
        $this->emailOwnerProviders[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner($email)
    {
        $emailOwner = null;
        foreach ($this->emailOwnerProviders as $emailOwnerProvider) {
            $emailOwner = $emailOwnerProvider->findEmailOwner($this->em, $email);
            if ($emailOwner !== null) {
                break;
            }
        }

        return $emailOwner;
    }
}
