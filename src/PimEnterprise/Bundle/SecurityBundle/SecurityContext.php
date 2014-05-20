<?php

namespace PimEnterprise\Bundle\SecurityBundle;

use Symfony\Component\Security\Core\SecurityContext as BaseSecurityContext;

/**
 * Override security context to add shortcut methods
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SecurityContext extends BaseSecurityContext
{
    /**
     * Get the authenticated user (or null if not)
     *
     * @return mixed|null
     */
    public function getUser()
    {
        if (null === $token = $this->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Get different roles of a user
     *
     * @return Role|null
     */
    public function getRoles()
    {
        if (null === $user = $this->getUser()) {
            return null;
        }

        return $user->getRoles();
    }
}
