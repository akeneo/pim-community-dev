<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\EventSubscriber;

use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository;

/**
 * This listener is used to listen to insert or delete
 * event from Doctrine to purge attribute list cache
 */
class AttributeCacheListener implements EventSubscriber
{
    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $cacheDriver = $entityManager->getConfiguration()->getResultCacheImpl();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof AbstractAttribute) {
                $cacheDriver->delete(AttributeRepository::getAttributesListCacheId($entity->getEntityType()));

                return;
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof AbstractAttribute) {
                $cacheDriver->delete(AttributeRepository::getAttributesListCacheId($entity->getEntityType()));

                return;
            }
        }
    }
}
