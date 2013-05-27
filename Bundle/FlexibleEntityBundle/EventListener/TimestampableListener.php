<?php
namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;

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
        if ($entity instanceof TimestampableInterface) {
            $entity->setCreated(new \DateTime('now', new \DateTimeZone('UTC')));
            $entity->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * Before update
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof \Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface) {
            $entity->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }
}
