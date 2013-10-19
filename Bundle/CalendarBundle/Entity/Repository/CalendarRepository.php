<?php

namespace Oro\Bundle\CalendarBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

class CalendarRepository extends EntityRepository
{
    /**
     * Gets user's calendar
     *
     * @param int $userId
     * @return Calendar
     */
    public function findByUser($userId)
    {
        return $this->findOneBy(array('owner' => $userId));
    }

    /**
     * @param int $calendarId
     * @return QueryBuilder
     */
    public function getConnectionsQueryBuilder($calendarId)
    {
        return $this->getEntityManager()->getRepository('OroCalendarBundle:CalendarConnection')->createQueryBuilder('a')
            ->select(
                'a.color, a.backgroundColor'
                . ', ac.id as calendar, ac.name as calendarName'
                . ', u.id as owner, u.firstName as ownerFirstName, u.lastName as ownerLastName'
            )
            ->innerJoin('a.calendar', 'c')
            ->innerJoin('a.connectedCalendar', 'ac')
            ->innerJoin('ac.owner', 'u')
            ->where('c.id = :id')
            ->orderBy('a.createdAt')
            ->setParameter('id', $calendarId);
    }
}
