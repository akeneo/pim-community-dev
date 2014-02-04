<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;

/**
 * Mark the indexed values for the entity as outdated once a value has been loaded from the DB
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OutdateIndexedValuesListener implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad'
        );
    }

    /**
     * After load
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof FlexibleValueInterface) {
            $flexibleEntity = $entity->getEntity();
            if (is_object($flexibleEntity)) {
                $flexibleEntity->markIndexedValuesOutdated();
            }
        } else if ($entity instanceof FlexibleInterface) {
            $entity->markIndexedValuesOutdated();
        }
    }
}
