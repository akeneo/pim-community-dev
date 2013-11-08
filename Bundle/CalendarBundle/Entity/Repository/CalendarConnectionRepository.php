<?php

namespace Oro\Bundle\CalendarBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;

class CalendarConnectionRepository extends EntityRepository
{
    /**
     * Gets CalendarConnection object represents a connection between the given calendars
     *
     * @param $calendarId
     * @param $connectedCalendarId
     * @return CalendarConnection
     */
    public function findByRelation($calendarId, $connectedCalendarId)
    {
        return $this->findOneBy(array('calendar' => $calendarId, 'connectedCalendar' => $connectedCalendarId));
    }

    /**
     * Returns a query builder which can be used to get a list of connected calendars
     *
     * @param int $calendarId
     * @param int|null $userId
     * @return QueryBuilder
     */
    public function getConnectionListQueryBuilder($calendarId, $userId = null)
    {
        $qb = $this->createQueryBuilder('calendarConnection')
            ->select('calendarConnection, connectedCalendar, owner')
            ->innerJoin('calendarConnection.calendar', 'calendar')
            ->innerJoin('calendarConnection.connectedCalendar', 'connectedCalendar')
            ->innerJoin('connectedCalendar.owner', 'owner')
            ->where('calendar.id = :id')
            ->orderBy('calendarConnection.createdAt')
            ->setParameter('id', $calendarId);

        if (null !== $userId) {
            $qb->andWhere('owner.id = :userId')->setParameter('userId', $userId);
        }

        return $qb;
    }
}
