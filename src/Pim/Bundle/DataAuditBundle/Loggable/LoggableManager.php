<?php

namespace Pim\Bundle\DataAuditBundle\Loggable;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager as OroLoggableManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Doctrine\ORM\EntityManager;

/**
 * Override loggable manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoggableManager extends OroLoggableManager
{
    /**
     * @param string         $logEntityClass
     * @param ConfigProvider $auditConfigProvider
     */
    public function __construct(
        $logEntityClass,
        ConfigProvider $auditConfigProvider
    ) {
        $this->auditConfigProvider = $auditConfigProvider;
        $this->logEntityClass      = $logEntityClass;
    }

    /**
     * @param EntityManager $em
     */
    public function handleLoggable(EntityManager $em)
    {
        $this->em = $em;
        $uow      = $em->getUnitOfWork();

        $collections = array_merge($uow->getScheduledCollectionUpdates(), $uow->getScheduledCollectionDeletions());
        foreach ($collections as $collection) {
            if (!$this->isSkipped($collection)) {
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
     * Check if entity must be skipped, disable it for PIM entities
     *
     * @param object $entity
     *
     * @return boolean
     */
    protected function isSkipped($entity)
    {
        $className = get_class($entity);
        if (strpos($className, 'Pim') !== 1) {
            return true;
        }

        return false;
    }
}
