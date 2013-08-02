<?php

namespace Oro\Bundle\EmailBundle\Entity\Manager;

use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

/**
 * Provides a set of method to manage email addresses.
 */
class EmailAddressManager implements EmailOwnerProviderInterface
{
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
    public function findEmailOwner($emailAddress)
    {
        $emailOwner = null;
        foreach ($this->emailOwnerProviders as $emailOwnerProvider) {
            $emailOwner = $emailOwnerProvider->findEmailOwner($emailAddress);
            if ($emailOwner !== null) {
                break;
            }
        }

        return $emailOwner;
    }
}
