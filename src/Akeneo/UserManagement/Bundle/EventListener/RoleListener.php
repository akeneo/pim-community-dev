<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\Model\Role;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink;

class RoleListener
{
    /**
     * @var \Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink
     */
    protected $aclSidManagerLink;

    public function __construct(ServiceLink $aclSidManagerLink)
    {
        $this->aclSidManagerLink = $aclSidManagerLink;
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof Role && $eventArgs->hasChangedField('role')) {
            $oldRoleName = $eventArgs->getOldValue('role');
            $newRoleName = $eventArgs->getNewValue('role');
            /** @var $aclSidManager AclSidManager */
            $aclSidManager = $this->aclSidManagerLink->getService();
            $aclSidManager->updateSid($aclSidManager->getSid($newRoleName), $oldRoleName);
        }
    }
}
