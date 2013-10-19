<?php

namespace Oro\Bundle\CalendarBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;
use Oro\Bundle\UserBundle\Entity\User;

class EntitySubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            //@codingStandardsIgnoreStart
            Events::onFlush
            //@codingStandardsIgnoreEnd
        );
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $needChangeSetsComputing = false;
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof User) {
                // create a default calendar to a new user
                $calendar = new Calendar();
                $calendar->setOwner($entity);
                // connect the calendar to itself
                $calendar->addConnection(new CalendarConnection($calendar));
                $em->persist($calendar);
                $needChangeSetsComputing = true;
            }
        }
        if ($needChangeSetsComputing) {
            $uow->computeChangeSets();
        }
    }
}
