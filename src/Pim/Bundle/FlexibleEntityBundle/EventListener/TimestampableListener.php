<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;

/**
 * Aims to add timestambable behavior
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        if ($entity instanceof \Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue) {
            $flexible = $entity->getEntity();
            if ($flexible !== null) {
                $this->updateFlexibleFields($args->getEntityManager(), $flexible, array('updated'));
            }
        }

        if ($entity instanceof \Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface) {
            $entity->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * Update flexible fields when a value is updated
     *
     * @param ObjectManager $manager
     * @param Flexible      $flexible
     * @param array         $fields
     */
    protected function updateFlexibleFields(ObjectManager $manager, AbstractFlexible $flexible, $fields)
    {
        $uow     = $manager->getUnitOfWork();
        $now     = new \DateTime('now', new \DateTimeZone('UTC'));
        $changes = array();
        foreach ($fields as $field) {
            $changes[$field] = array(null, $now);
        }
        $uow->scheduleExtraUpdate($flexible, $changes);
    }
}
