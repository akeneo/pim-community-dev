<?php

namespace Oro\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EmailNotificationRepository
 */
class EmailNotificationRepository extends EntityRepository
{
    /**
     * @return ArrayCollection
     */
    public function getRules()
    {
        $rules = $this->createQueryBuilder('emn')
            ->select(array('emn', 'event'))
            ->leftJoin('emn.event', 'event')
            ->getQuery()
            ->getResult();

        return new ArrayCollection($rules);
    }
}
