<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleListener
{
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof Role && $eventArgs->hasChangedField('role')) {
            throw new \RuntimeException('Unable to change role name at runtime');
        }
    }
}
