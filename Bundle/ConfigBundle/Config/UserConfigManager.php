<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Symfony\Component\Security\Core\SecurityContextInterface;

class UserConfigManager extends ConfigManager
{
    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * DI setter for security context
     *
     * @param SecurityContextInterface $security
     */
    public function setSecurity(SecurityContextInterface $security)
    {
        $this->security = $security;

        // if we have a user - try to merge his scoped settings into global settings array
        if ($token = $this->security->getToken()) {
            if (is_object($user = $token->getUser())) {
                foreach ($user->getGroups() as $group) {
                    $this->loadStoredSettings(get_class($group), $group->getId());
                }

                $this->loadStoredSettings(get_class($user), $user->getId());
            }
        }
    }
}
