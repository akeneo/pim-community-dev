<?php

namespace Oro\Bundle\OrganizationBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository;

class BusinessUnitListener
{
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof BusinessUnit) {
            /** @var $repository  BusinessUnitRepository */
            $repository = $eventArgs->getEntityManager()->getRepository('OroOrganizationBundle:BusinessUnit');
            if ($repository->getBusinessUnitsCount() <= 1) {
                throw new \RuntimeException('Unable to remove the last business unit');
            }
        }
    }
}
