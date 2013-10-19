<?php

namespace Oro\Bundle\CalendarBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CalendarBundle\Entity\Calendar;

class CalendarEventRepository extends EntityRepository
{
    /**
     * @param int       $calendarId
     * @param \DateTime $start
     * @param \DateTime $end
     * @param bool      $includeConnectedCalendars
     * @return QueryBuilder
     */
    public function getEventsQueryBuilder(
        $calendarId,
        \DateTime $start,
        \DateTime $end,
        $includeConnectedCalendars = false
    ) {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->innerJoin('e.calendar', 'c')
            ->where('e.start >= :start AND e.end < :end')
            ->orderBy('c.id, e.start')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($includeConnectedCalendars) {
            $qbAC = $this->getEntityManager()->getRepository('OroCalendarBundle:Calendar')->createQueryBuilder('c1')
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
}
