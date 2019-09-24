<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AbstractAclManager
{
    /**
     * Constructs SID (an object implements SecurityIdentityInterface) based on the given identity
     *
     * @param string|RoleInterface|UserInterface|TokenInterface $identity
     * @throws \InvalidArgumentException
     * @return SID
     */
    public function getSid($identity)
    {
        if (is_string($identity)) {
            return new RoleSecurityIdentity($identity);
        } elseif ($identity instanceof RoleInterface) {
            return new RoleSecurityIdentity($identity->getRole());
        } elseif ($identity instanceof UserInterface) {
            return UserSecurityIdentity::fromAccount($identity);
        } elseif ($identity instanceof TokenInterface) {
            return UserSecurityIdentity::fromToken($identity);
        }

        throw new \InvalidArgumentException(
            sprintf(
                '$identity must be a string or implement one of RoleInterface, UserInterface, TokenInterface'
                . ' (%s given)',
                is_object($identity) ? get_class($identity) : gettype($identity)
            )
        );
    }
}
