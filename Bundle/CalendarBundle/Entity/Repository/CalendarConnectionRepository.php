<?php

namespace Oro\Bundle\CalendarBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
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
}
