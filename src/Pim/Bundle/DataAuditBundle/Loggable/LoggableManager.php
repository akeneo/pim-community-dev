<?php

namespace Pim\Bundle\DataAuditBundle\Loggable;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager as OroLoggableManager;

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
     * {@inheritDoc}
     */
    protected function calculateCollectionData(PersistentCollection $collection)
    {
        if (!$this->isSkipped($collection)) {
            parent::calculateCollectionData($collection);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createLogEntity($action, $entity)
    {
        if (!$this->isSkipped($entity)) {
            parent::createLogEntity($action, $entity);
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
