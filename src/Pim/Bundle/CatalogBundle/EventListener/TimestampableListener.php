<?php
namespace Pim\Bundle\CatalogBundle\EventListener;

use Oro\Bundle\FlexibleEntityBundle\EventListener\TimestampableListener as BaseTimestampableListener;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;

/**
 * Aims to add timestambable behavior
*/
class TimestampableListener extends BaseTimestampableListener
{
    /**
     * Before insert
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof TimestampableInterface) {
            $entity->setCreated(new \DateTime('now'));
            $entity->setUpdated(new \DateTime('now'));
        }
    }

    /**
     * Update flexible fields when a value is updated
     *
     * @param ObjectManager $om
     * @param Flexible      $flexible
     * @param array         $fields
     */
    protected function updateFlexibleFields(ObjectManager $objectManager, AbstractFlexible $flexible, $fields)
    {
        $unitOfWork  = $objectManager->getUnitOfWork();
        $now  = new \DateTime('now');
        $changes = array();
        foreach ($fields as $field) {
            $changes[$field]= array(null, $now);
        }
        $unitOfWork->scheduleExtraUpdate($flexible, $changes);
    }
}
