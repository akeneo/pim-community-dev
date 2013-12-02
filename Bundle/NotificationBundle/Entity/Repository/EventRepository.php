<?php

namespace Oro\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getEventNames()
    {
        return $this->getEventNamesQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getEventNamesChoices()
    {
        $options = [];
        $eventNames = $this->getEventNamesQuery()->getArrayResult();

        foreach ((array) $eventNames as $value) {
            $options[$value['id']] = $value['name'];
        }

        return $options;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    protected function getEventNamesQuery()
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.name')
            ->getQuery();
    }
}
