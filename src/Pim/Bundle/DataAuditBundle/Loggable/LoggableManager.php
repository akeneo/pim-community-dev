<?php

namespace Pim\Bundle\DataAuditBundle\Loggable;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager as OroLoggableManager;
use Doctrine\ORM\EntityManager;

/**
 * Override loggable manager
 */
class LoggableManager extends OroLoggableManager
{
    /**
     * @param EntityManager $em
     */
    public function handleLoggable(EntityManager $em)
    {
        $this->em = $em;
        $uow      = $em->getUnitOfWork();

        $collections = array_merge($uow->getScheduledCollectionUpdates(), $uow->getScheduledCollectionDeletions());
        foreach ($collections as $collection) {
            if (!$this->isSkipped($entity)) {
                $this->calculateCollectionData($collection);
            }
        }
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$this->isSkipped($entity)) {
                $this->createLogEntity(self::ACTION_CREATE, $entity);
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$this->isSkipped($entity)) {
                $this->createLogEntity(self::ACTION_UPDATE, $entity);
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$this->isSkipped($entity)) {
                $this->createLogEntity(self::ACTION_REMOVE, $entity);
            }
        }
    }

    /**
     * Check if entity must be skipped
     *
     * @param object $entity
     *
     * @return boolean
     */
    protected function isSkipped($entity)
    {
        $className = get_class($entity);
        if (strpos($className, 'Pim') === 1) {
die('SKIP');
        }

    }
}
