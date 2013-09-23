<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AclSidManager
{
    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * Constructor
     *
     * @param MutableAclProvider $aclProvider
     */
    public function __construct(
        MutableAclProvider $aclProvider = null
    ) {
        $this->aclProvider = $aclProvider;
    }

    /**
     * Indicates whether ACL based security is enabled or not
     *
     * @return bool
     */
    public function isAclEnabled()
    {
        return $this->aclProvider !== null;
    }

    /**
     * Constructs SID (an object implements SecurityIdentityInterface) based on the given identity
     *
     * @param string|RoleInterface|UserInterface|TokenInterface $identity
     * @throws \InvalidArgumentException
     * @return SID
     */
    public static function getSid($identity)
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

    /**
     * Updates the security identity name.
     *
     * @param SID $sid An implementation of SecurityIdentityInterface created using the new name
     * @param string $oldName The old security identity name.
     *                        It is the user's username if $sid is UserSecurityIdentity
     *                        or the role name if $sid is RoleSecurityIdentity
     */
    public function updateSid(SID $sid, $oldName)
    {
        if ($this->isAclEnabled()) {
            $this->aclProvider->updateSecurityIdentity($sid, $oldName);
        }
    }

    /**
     * Deletes the given security identity.
     *
     * @param SID $sid
     */
    public function deleteSid(SID $sid)
    {
        if ($this->isAclEnabled()) {
            $this->aclProvider->deleteSecurityIdentity($sid);
        }
    }
}
