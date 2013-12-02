<?php

namespace Oro\Bundle\CalendarBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class CalendarEventRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get a list of calendar events
     *
     * @param int       $calendarId
     * @param \DateTime $startDate                   Start date
     * @param \DateTime $endDate                     End date
     * @param bool      $includingConnectedCalendars If true events from connected calendars will be returned as well
     * @return QueryBuilder
     */
    public function getEventListQueryBuilder(
        $calendarId,
        $startDate,
        $endDate,
        $includingConnectedCalendars = false
    ) {
        $qb = $this->createQueryBuilder('e')
            ->select('c.id as calendar, e.id, e.title, e.start, e.end, e.allDay, e.reminder')
            ->innerJoin('e.calendar', 'c')
            ->where('e.start >= :start AND e.end < :end')
            ->orderBy('c.id, e.start')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);
        if ($includingConnectedCalendars) {
            $calendarRepo = $this->getEntityManager()->getRepository('OroCalendarBundle:Calendar');
            $qbAC         = $calendarRepo->createQueryBuilder('c1')
                ->select('ac.id')
                ->innerJoin('c1.connections', 'a')
                ->innerJoin('a.connectedCalendar', 'ac')
                ->where('c1.id = :id')
                ->setParameter('id', $calendarId);

            $qb
                ->andWhere($qb->expr()->in('c.id', $qbAC->getDQL()))
                ->setParameter('id', $calendarId);
        } else {
            $qb
                ->andWhere('c.id = :id')
                ->setParameter('id', $calendarId);
        }

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get a list of calendar events
     * for which a remind notification need to be sent.
     *
     * @param \DateTime $currentTime The current date/time in UTC
     * @return QueryBuilder
     */
    public function getEventsToRemindQueryBuilder($currentTime)
    {
        return $this->createQueryBuilder('e')
            ->select('e, c, u')
            ->innerJoin('e.calendar', 'c')
            ->innerJoin('c.owner', 'u')
            ->where('e.remindAt <= :current AND e.start > :current AND e.reminded = :reminded')
            ->setParameter('current', $currentTime)
            ->setParameter('reminded', false);
    }
}
