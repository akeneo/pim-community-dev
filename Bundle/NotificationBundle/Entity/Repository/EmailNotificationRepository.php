<?php

namespace Oro\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * EmailNotificationRepository
 */
class EmailNotificationRepository extends EntityRepository
{
    public function getRulesByCriteria($entityName, $eventName)
    {
        return $this->createQueryBuilder('emn')
            ->select(array('emn', 'event'))
            ->leftJoin('emn.event', 'event')
            ->where('emn.entityName = :entityName')
            ->andWhere('event.name = :eventName')
            ->setParameter('entityName', $entityName)
            ->setParameter('eventName', $eventName)
            ->getQuery()
            ->getResult();
    }
}
