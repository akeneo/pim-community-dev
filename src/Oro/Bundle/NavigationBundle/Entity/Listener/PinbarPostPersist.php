<?php

namespace Oro\Bundle\NavigationBundle\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\NavigationBundle\Entity\PinbarTab;

class PinbarPostPersist
{
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\PinbarTab */
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // perhaps you only want to act on some "PinbarTab" entity
        if ($entity instanceof PinbarTab) {
            /** @var $repo \Oro\Bundle\NavigationBundle\Entity\Repository\PinbarTabRepository */
            $repo = $entityManager->getRepository(get_class($entity));
            $repo->incrementTabsPositions($entity->getItem()->getUser(), $entity->getItem()->getId());
        }
    }
}
