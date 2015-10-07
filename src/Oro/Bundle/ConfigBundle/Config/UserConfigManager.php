<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserConfigManager extends ConfigManager
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * DI setter for token storage
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function setSecurity(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        // if we have a user - try to merge his scoped settings into global settings array
        if ($token = $this->tokenStorage->getToken()) {
            if (is_object($user = $token->getUser())) {
                foreach ($user->getGroups() as $group) {
                    $this->loadStoredSettings('group', $group->getId());
                }

                $this->loadStoredSettings('user', $user->getId());
            }
        }
    }

    /**
     * @return string
     */
    public function getScopedEntityName()
    {
        return 'user';
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return 0;
    }
}
