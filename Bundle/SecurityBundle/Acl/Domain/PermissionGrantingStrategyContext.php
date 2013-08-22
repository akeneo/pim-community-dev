<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Provides an interface which can be implemented by AclVoter to allow
 * the underlying permission granting strategy to get
 * an object which is the subject of the current voting operation
 * and the security token of the current voting operation.
 */
interface PermissionGrantingStrategyContext
{
    /**
     * Gets the current object from a context
     *
     * @return mixed
     */
    public function getObject();

    /**
     * Gets the security token from a context
     *
     * @return TokenInterface
     */
    public function getSecurityToken();
}
