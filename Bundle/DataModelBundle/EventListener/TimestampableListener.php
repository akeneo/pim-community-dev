<?php
namespace Oro\Bundle\DataModelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Aims to add timestambable behavior
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TimestampableListener implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Before insert
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof \Oro\Bundle\DataModelBundle\Model\Behavior\TimestampableInterface) {
            $entity->setCreated(new \DateTime());
            $entity->setUpdated(new \DateTime());
        }
    }

    /**
     * Before update
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof \Oro\Bundle\DataModelBundle\Model\Behavior\TimestampableInterface) {
            $entity->setUpdated(new \DateTime());
        }
    }
}