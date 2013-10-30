<?php

namespace Oro\Bundle\CalendarBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CalendarBundle\Entity\Calendar;

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
}
