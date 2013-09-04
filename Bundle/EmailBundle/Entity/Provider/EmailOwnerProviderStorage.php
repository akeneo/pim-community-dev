<?php

namespace Oro\Bundle\EmailBundle\Entity\Provider;

/**
 * A storage of email owner providers
 */
class EmailOwnerProviderStorage
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
     * Get all email owner providers
     *
     * @return EmailOwnerProviderInterface[]
     */
    public function getProviders()
    {
        return $this->emailOwnerProviders;
    }
}
