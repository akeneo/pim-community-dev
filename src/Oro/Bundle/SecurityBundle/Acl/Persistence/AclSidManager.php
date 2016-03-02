<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;

class AclSidManager extends AbstractAclManager
{
    const ROLE_DISABLED_FLAG = '-DISABLED-';

    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * Constructor
     *
     * @param MutableAclProvider $aclProvider
     */
    public function __construct(MutableAclProvider $aclProvider = null)
    {
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
            if ($sid instanceof RoleSecurityIdentity) {
                /**
                 * Marking removed Role as Disabled instead of delete, because straight deleting role identity breaks
                 * ace indexes
                 * TODO: Create a job to remove marked role identities and rebuild ace indexes
                 */
                $disabledSid = new RoleSecurityIdentity($sid->getRole() . uniqid(self::ROLE_DISABLED_FLAG));
                $this->aclProvider->updateSecurityIdentity($disabledSid, $sid->getRole());
            } else {
                $this->aclProvider->deleteSecurityIdentity($sid);
            }
        }
    }
}
